<?php
// index.php - 生成 static/setting.js 的初始化器

$settingPath   = __DIR__ . '/static/setting.js';
$indexHtmlPath = __DIR__ . '/index.html';

// ---------------------------
// 若 setting.js 已存在 → 不再显示表单，直接进入 panel
// ---------------------------
if (file_exists($settingPath)) {
    header("Location: /index.html");
    exit();
}

// ---------------------------
// 若用户提交表单 → 生成文件 + 更新 index.html
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单字段
    $serverUrl      = trim($_POST['serverUrl'] ?? '');
    $landPage       = trim($_POST['landPage'] ?? '');
    $appName        = trim($_POST['appName'] ?? '');
    $appDesc        = trim($_POST['appDesc'] ?? '');
    $appVersion     = trim($_POST['appVersion'] ?? '');
    $appColor       = trim($_POST['appColor'] ?? '');
    $appTheme       = trim($_POST['appTheme'] ?? '');
    $showRegInvite  = trim($_POST['showRegInvite'] ?? '0');
    $footerJs       = $_POST['footerJs'] ?? '';

    // static 目录不存在则创建
    if (!is_dir(__DIR__ . '/static')) {
        mkdir(__DIR__ . '/static', 0777, true);
    }

    // ---------------------------
    // 写入 setting.js
    // ---------------------------
    $content = <<<JS
// 主题前端配置文件

window.EnvConfig = {
  serverUrl: "{$serverUrl}",
  landPage: "{$landPage}",

  // -------------------------
  // 👥 是否显示邀请注册（0=隐藏, 1=显示）
  // -------------------------
  showRegInvite: "{$showRegInvite}",

  // -------------------------
  // 🌓 默认主题
  // auto：自动跟随系统
  // light：亮色模式
  // dark：暗色模式
  // -------------------------
  appTheme: "{$appTheme}",

  // -------------------------
  // 🎨 主题主色（Aurora 提供的颜色名）
  // 可选：daybreakBlue / cyan / polarGreen / lime / sunriseYellow / calendulaGold / sunsetOrange /volcano / dustRed
  // -------------------------
  appColor: "{$appColor}",

  // -------------------------
  // 🏷️ 面板显示名称（Logo旁显示）
  // -------------------------
  appName: "{$appName}",

  // -------------------------
  // 📄 面板描述（用于 SEO 与页面副标题）
  // -------------------------
  appDesc: "{$appDesc}",

  // -------------------------
  // 🖼️ 页面 favicon 图标（必须放在网站根目录）
  // -------------------------
  appLogo: "/favicon.png",

  // -------------------------
  // 🔢 当前面板版本号（可自定义）
  // -------------------------
  appVersion: "{$appVersion}",

  // -------------------------
  // 📱 客户端下载链接（留空则隐藏）
  // -------------------------
  clientIOS: "",
  clientAndroid: "",
  clientWindows: "",
  clientMacOS: "",
  clientOpenwrt: "",
  clientLinux: "",

  // -------------------------
  // 📁 静态资源路径，一般不需要改
  // 所有前端 CSS/JS 都从这里加载
  // -------------------------
  staticUrl: "/static"
};
JS;

    file_put_contents($settingPath, $content);

    // ---------------------------
    // ① 修改 index.html 中 setting.js 的版本号
    // ---------------------------
    if (file_exists($indexHtmlPath)) {
        $html    = file_get_contents($indexHtmlPath);
        $version = date('YmdHis'); // 如 20251121021659

        // 用完整标签做替换，更安全
        $pattern     = '#<script\s+src="/static/setting\.js\?v=[^"]*"></script>#i';
        $replacement = '<script src="/static/setting.js?v=' . $version . '"></script>';
        $html        = preg_replace($pattern, $replacement, $html, 1);

        // ---------------------------
        // ② 若填写了 footer JS，则插入到 </body> 之前
        // ---------------------------
        $footerJs = trim($footerJs);
        if ($footerJs !== '') {
            // 去掉用户脚本里的 </body> 和 </html>，防止重复标签
            $footerJsClean = preg_replace('#</body>|</html>#i', '', $footerJs);

            // 插入到 </body> 前
            $html = preg_replace(
                '#</body>#i',
                "\n" . $footerJsClean . "\n</body>",
                $html,
                1,
                $countBody
            );

            // 如果没有 </body>（极端情况），直接追加到末尾
            if ($countBody === 0) {
                $html .= "\n" . $footerJsClean . "\n";
            }
        }

        file_put_contents($indexHtmlPath, $html);
    }

    // 简洁的成功页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="utf-8" />
        <title>主题配置完成</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <style>
          * { box-sizing: border-box; }
          body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #0f172a, #020617);
            color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
          }
          .card {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.95);
            border-radius: 16px;
            padding: 24px 22px 20px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.9);
          }
          h1 {
            margin: 0 0 8px;
            font-size: 20px;
          }
          p {
            margin: 4px 0;
            font-size: 14px;
            color: #9ca3af;
          }
          code {
            background: rgba(15,23,42,0.9);
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 12px;
          }
          .btn {
            margin-top: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 16px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.5);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
          }
          .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.65);
            filter: brightness(1.03);
          }
          .btn:active {
            transform: translateY(0);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.55);
            filter: brightness(0.97);
          }
        </style>
    </head>
    <body>
      <div class="card">
        <h1>配置已完成 ✅</h1>
        <p>文件 <code>static/setting.js</code> 已生成，并已更新 <code>index.html</code> 中的版本号。</p>
        <p>如填写了页脚 JS，也已插入到 <code>&lt;/body&gt;</code> 之前。</p>
        <form action="/" method="get">
          <button class="btn" type="submit">进入面板首页</button>
        </form>
      </div>
    </body>
    </html>
    <?php
    exit();
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <title>初始化主题配置</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
      * { box-sizing: border-box; }
      body {
        margin: 0;
        min-height: 100vh;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: radial-gradient(circle at top, #1d4ed8 0, #020617 55%);
        color: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
      }
      .card {
        width: 100%;
        max-width: 520px;
        background: rgba(15, 23, 42, 0.96);
        border-radius: 18px;
        padding: 22px 22px 20px;
        border: 1px solid rgba(148, 163, 184, 0.4);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.95);
      }
      h1 {
        margin: 0 0 8px;
        font-size: 20px;
      }
      .subtitle {
        margin: 0 0 18px;
        font-size: 13px;
        color: #9ca3af;
      }
      form {
        margin-top: 4px;
      }
      .field {
        margin-bottom: 12px;
      }
      label {
        display: block;
        font-size: 13px;
        margin-bottom: 4px;
        color: #d1d5db;
      }
      .hint {
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 4px;
      }
      input[type="text"],
      textarea {
        width: 100%;
        padding: 8px 10px;
        font-size: 13px;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.7);
        background: #020617;
        color: #e5e7eb;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        resize: vertical;
      }
      input[type="text"]::placeholder,
      textarea::placeholder {
        color: #6b7280;
      }
      input[type="text"]:focus,
      textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.7);
        background: #020617;
      }
      .row {
        display: flex;
        gap: 12px;
      }
      .row .field {
        flex: 1;
      }
      @media (max-width: 640px) {
        .row {
          flex-direction: column;
        }
      }
      .footer {
        margin-top: 6px;
        font-size: 11px;
        color: #9ca3af;
      }
      code {
        background: rgba(15,23,42,0.9);
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 11px;
      }
      .btn {
        margin-top: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 16px;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: #fff;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.5);
        transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
      }
      .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(37, 99, 235, 0.65);
        filter: brightness(1.03);
      }
      .btn:active {
        transform: translateY(0);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.55);
        filter: brightness(0.97);
      }
    </style>
</head>
<body>
  <div class="card">
    <h1>初始化主题配置</h1>
    <p class="subtitle">
      第一次使用时，请填写下面几项基础信息，我们会自动生成
      <code>static/setting.js</code>，并可选注入页脚 JS。
    </p>

    <form method="POST">
      <div class="field">
        <label for="serverUrl">后端 API 域名（serverUrl）</label>
        <div class="hint">填 v2board 主站域名，例如：<code>https://vvv.com</code></div>
        <input
          id="serverUrl"
          name="serverUrl"
          type="text"
          placeholder="例如：https://vvv.com"
          required
        />
      </div>

      <div class="field">
        <label for="landPage">落地页（landPage）</label>
        <div class="hint">进入宣传落地页或者登录页，例如：<code>index / login</code></div>
        <input
          id="landPage"
          name="landPage"
          type="text"
          placeholder="index"
        />
      </div>

      <div class="row">
        <div class="field">
          <label for="appName">面板名称（appName）</label>
          <div class="hint">显示在 Logo 旁，例如：xx云</div>
          <input
            id="appName"
            name="appName"
            type="text"
            placeholder="例如：xx云"
          />
        </div>

        <div class="field">
          <label for="appVersion">版本号（appVersion）</label>
          <div class="hint">自定义版本号，例如：xxx-v1.01</div>
          <input
            id="appVersion"
            name="appVersion"
            type="text"
            placeholder="例如：xxx-v1.01"
          />
        </div>
      </div>

      <div class="field">
        <label for="appDesc">页面描述（appDesc）</label>
        <div class="hint">用于 SEO 和副标题，例如：xxx Cloud 用户中心</div>
        <input
          id="appDesc"
          name="appDesc"
          type="text"
          placeholder="例如：xxx Cloud 用户中心"
        />
      </div>

      <div class="row">
        <div class="field">
          <label for="appColor">主题主色（appColor）</label>
          <div class="hint">如：daybreakBlue / cyan / polarGreen / lime / sunriseYellow / calendulaGold / sunsetOrange / volcano / dustRed</div>
          <input
            id="appColor"
            name="appColor"
            type="text"
            value="default"
            placeholder="默认：default"
          />
        </div>

        <div class="field">
          <label for="appTheme">默认主题（appTheme）</label>
          <div class="hint">可填：auto / light / dark</div>
          <input
            id="appTheme"
            name="appTheme"
            type="text"
            value="auto"
            placeholder="默认：auto"
          />
        </div>
      </div>

      <div class="field">
        <label for="showRegInvite">显示邀请注册（showRegInvite）</label>
        <div class="hint">0 = 隐藏，1 = 显示</div>
        <input
          id="showRegInvite"
          name="showRegInvite"
          type="text"
          value="0"
        />
      </div>

      <div class="field">
        <label for="footerJs">页脚 JS（可选，插入到 &lt;/body&gt; 前）</label>
        <div class="hint">
          可以直接粘贴统计 / 客服脚本，例如 Tawk.to 代码。
          如包含 <code>&lt;/body&gt;</code> / <code>&lt;/html&gt;</code> 会自动去掉。
        </div>
        <textarea
          id="footerJs"
          name="footerJs"
          rows="6"
          placeholder="例如：&lt;script&gt;...&lt;/script&gt;"
        ></textarea>
      </div>

      <button class="btn" type="submit">生成 setting.js 并继续</button>

      <div class="footer">
        生成后会创建 <code>static/setting.js</code> 并更新
        <code>&lt;script src=&quot;/static/setting.js?v=...&quot;&gt;</code> 的版本号。<br>
        如需重新配置，只需删除 <code>static/setting.js</code> 并重新访问本页面。
      </div>
    </form>
  </div>
</body>
</html>
