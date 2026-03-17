<?php
// Get OTA BIN file list
$otaDir = __DIR__ . '/ota';
$files = [];

if (is_dir($otaDir)) {
    foreach (glob($otaDir . '/*.bin') as $path) {
        $name = basename($path);

        // Extract version string from filename (e.g. xptouch.0.0.88.bin -> 0.0.88)
        $version = '';
        if (preg_match('/(\d+(?:\.\d+)+)/', $name, $matches)) {
            $version = $matches[1];
        }

        $files[] = [
            'name' => $name,
            'version' => $version,
            'size' => filesize($path),
            'mtime' => filemtime($path),
        ];
    }
}

// Sort by version (descending). If version is not detected, fall back to mtime.
usort($files, function ($a, $b) {
    if ($a['version'] !== '' && $b['version'] !== '') {
        // version_compare: larger version should come first
        return version_compare($b['version'], $a['version']);
    }
    return $b['mtime'] <=> $a['mtime'];
});

function formatBytes($bytes, $precision = 1)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>XTouch OTA Version History</title>
    <style>
      body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background-color: #101010;
        color: #f5f5f5;
        margin: 0;
        padding: 24px;
      }
      h1 {
        margin-top: 0;
        margin-bottom: 16px;
        font-size: 24px;
      }
      p.description {
        margin-top: 0;
        margin-bottom: 24px;
        color: #bbbbbb;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        background-color: #181818;
        border-radius: 8px;
        overflow: hidden;
      }
      thead {
        background: linear-gradient(90deg, #444444, #666666);
      }
      th,
      td {
        padding: 12px 16px;
        text-align: left;
        font-size: 14px;
      }
      th {
        font-weight: 600;
        color: #ffffff;
      }
      tbody tr:nth-child(even) {
        background-color: #202020;
      }
      tbody tr:nth-child(odd) {
        background-color: #181818;
      }
      tbody tr:hover {
        background-color: #2a2a2a;
      }
      a.download-btn {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 4px;
        background: #cccccc;
        color: #101010;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
      }
      a.download-btn:hover {
        background: #ffffff;
      }
      .empty {
        padding: 16px 0;
        color: #bbbbbb;
      }
      .container {
        max-width: 960px;
        margin: 0 auto;
      }
    </style>
    <script
      type="module"
      src="https://unpkg.com/esp-web-tools@9.4.0/dist/web/install-button.js?module"
    ></script>
  </head>
  <body>
    <div class="container">
      <h1>OTA Version History (2.8)</h1>
      <p class="description">
        This is a list of BIN files in the <code>ota</code> folder. Click the file name or button to download.
        After downloading, rename the file to <code>firmware.bin</code> and copy it to the SD card to update the firmware.
      </p>

      <?php if (empty($files)): ?>
        <div class="empty">No OTA BIN files were found.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>File name</th>
              <th>Size</th>
              <th>Last modified</th>
              <th>Download</th>
              <th>Write (Web)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($files as $file): ?>
              <tr>
                <td>
                  <a
                    href="<?php echo 'ota/' . rawurlencode($file['name']); ?>"
                    download="<?php echo htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8'); ?>"
                    style="color: #eab638; text-decoration: none;"
                  >
                    <?php echo htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                </td>
                <td><?php echo htmlspecialchars(formatBytes($file['size']), ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                  <?php
                    echo htmlspecialchars(
                      date('Y-m-d H:i:s', $file['mtime']),
                      ENT_QUOTES,
                      'UTF-8'
                    );
                  ?>
                </td>
                <td>
                  <a
                    class="download-btn"
                    href="<?php echo 'ota/' . rawurlencode($file['name']); ?>"
                    download="<?php echo htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8'); ?>"
                  >
                    Download
                  </a>
                </td>
                <td>
                  <?php
                    $version = $file['version'];
                    $webusbBin = __DIR__ . '/webusb/xptouch.web.' . $version . '.bin';
                    if ($version !== '' && is_file($webusbBin)):
                  ?>
                    <esp-web-install-button
                      manifest="<?php echo 'webusb/manifest.php?version=' . htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    </esp-web-install-button>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </body>
</html>

