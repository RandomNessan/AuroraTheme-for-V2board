// /static/custom.js

(function () {
  const SEGMENT = 'client/subscribe';      // 只要出现这个，就动手
  const OLD_PREFIX = '/api/v1';
  const NEW_PREFIX = '';      // 根据api路径配置内容自行修改填写

  console.log('[RandoPatch] custom.js loaded, starting patch…');

  function transformUrl(url) {
    if (!url || typeof url !== 'string') return url;
    if (!url.includes(SEGMENT)) return url;

    // 精准替换 /api/v1/client/subscribe → /rando/client/subscribe
    const oldFull = OLD_PREFIX + '/' + SEGMENT;
    const newFull = NEW_PREFIX + '/' + SEGMENT;

    if (url.includes(oldFull)) {
      const replaced = url.replaceAll(oldFull, newFull);
      console.log('[RandoPatch] URL replaced:', url, '=>', replaced);
      return replaced;
    }

    // 容错：没有 /api/v1 前缀，只是 .../client/subscribe，也抹掉前面的路径
    if (url.includes('/' + SEGMENT)) {
      const replaced = url.replace(/\/[^\/]*client\/subscribe/, newFull);
      console.log('[RandoPatch] URL generic replaced:', url, '=>', replaced);
      return replaced;
    }

    return url;
  }

  function patchOneElement(el) {
    if (!el || !el.getAttribute) return;

    // 1. 常见属性替换：href / value / data-clipboard-text
    ['href', 'value', 'data-clipboard-text'].forEach(attr => {
      const v = el.getAttribute(attr);
      if (v && v.includes(SEGMENT)) {
        const nv = transformUrl(v);
        if (nv !== v) {
          el.setAttribute(attr, nv);
          console.log('[RandoPatch] patched', attr, 'on', el.tagName, '=>', nv);
        }
      }
    });

    // 2. 文本内容替换：有的面板会直接把订阅链接显示成纯文本
    if (el.innerText && el.innerText.includes(SEGMENT)) {
      const oldText = el.innerText;
      const newText = transformUrl(oldText);
      if (newText !== oldText) {
        el.innerText = newText;
        console.log('[RandoPatch] patched innerText on', el.tagName, '=>', newText);
      }
    }
  }

  function patchAll() {
    document.querySelectorAll('*').forEach(patchOneElement);
  }

  // 首次加载
  document.addEventListener('DOMContentLoaded', function () {
    console.log('[RandoPatch] DOMContentLoaded');
    patchAll();
  });

  // 监听 SPA 的 DOM 变化（路由切换 / 弹窗等）
  const observer = new MutationObserver(muts => {
    muts.forEach(m => {
      m.addedNodes.forEach(node => {
        if (node.nodeType === 1) {
          patchOneElement(node);
          node.querySelectorAll &&
            node.querySelectorAll('*').forEach(patchOneElement);
        }
      });
    });
  });

  observer.observe(document.documentElement, {
    childList: true,
    subtree: true
  });

  // 保险：每 3 秒全局扫一遍
  setInterval(patchAll, 3000);

  // 3. Hook navigator.clipboard.writeText（若使用新 API 复制）
  if (navigator.clipboard && navigator.clipboard.writeText) {
    const origWriteText = navigator.clipboard.writeText.bind(navigator.clipboard);
    navigator.clipboard.writeText = function (text) {
      let newText = text;
      if (typeof text === 'string' && text.includes(SEGMENT)) {
        newText = transformUrl(text);
        console.log('[RandoPatch] clipboard.writeText patched:', text, '=>', newText);
      }
      return origWriteText(newText);
    };
    console.log('[RandoPatch] navigator.clipboard.writeText hooked');
  } else {
    console.log('[RandoPatch] navigator.clipboard.writeText not available, skip hook');
  }

  // 4. 再 Hook 一层 document.execCommand('copy')（老式复制方案）
  if (document.execCommand) {
    const origExecCommand = document.execCommand.bind(document);
    document.execCommand = function (command, showUI, valueArg) {
      if (typeof command === 'string' && command.toLowerCase() === 'copy') {
        try {
          const active = document.activeElement;
          if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) {
            const v = active.value;
            if (v && v.includes(SEGMENT)) {
              const nv = transformUrl(v);
              if (nv !== v) {
                active.value = nv;
                active.setSelectionRange(0, nv.length);
                console.log('[RandoPatch] execCommand(copy) patched INPUT/TEXTAREA value:', v, '=>', nv);
              }
            }
          } else {
            const sel = window.getSelection && window.getSelection();
            if (sel && sel.toString && sel.toString().includes(SEGMENT)) {
              const text = sel.toString();
              const nv = transformUrl(text);
              if (nv !== text) {
                const range = sel.getRangeAt(0);
                const tmp = document.createElement('span');
                tmp.textContent = nv;
                range.deleteContents();
                range.insertNode(tmp);
                sel.removeAllRanges();
                const newRange = document.createRange();
                newRange.selectNodeContents(tmp);
                sel.addRange(newRange);
                console.log('[RandoPatch] execCommand(copy) patched selection:', text, '=>', nv);
              }
            }
          }
        } catch (e) {
          console.warn('[RandoPatch] execCommand patch error:', e);
        }
      }
      return origExecCommand(command, showUI, valueArg);
    };
    console.log('[RandoPatch] document.execCommand hooked');
  }
})();
