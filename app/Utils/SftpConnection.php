<?php
/**
 * SFTP class.
 *
 * @package   App
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App\Utils;

/**
 * Should use absolute paths for remote paths.
 * 
 * Based on comments in https://www.php.net/manual/en/function.ssh2-sftp.php#71197
 * 
 * <code>
 * <?php
 * try {
 *  $sftp = new SFTPConnection("mbizon.eastus.cloudapp.azure.com", 22);
 *  $sftp->loginKey("cms-sftp-signed", "config/Keys/cms-sftp-signed.pub", "config/Keys/cms-sftp-signed.pem");
 * 
 *  $sftp->uploadFile("storage/index.html", "/signed/index.html");
 *  echo "List ." . PHP_EOL;
 *  print_r($sftp->listFiles("/signed", true));
 *  echo PHP_EOL;
 *  $sftp->downloadFile("/signed/index.html", "storage/file1");
 *  $sftp->deleteFile("/signed/index.html");
 * } catch (Exception $e) {
 *  echo $e->getMessage() . "\n";
 * }
 * ?>
 * </code>
 */
class SftpConnection
{
  private $connection;
  private $sftp;

  public function __construct($host, $port = 22)
  {
    $this->connection = @ssh2_connect($host, $port);
    if (!$this->connection)
      throw new \Exception("Could not connect to $host on port $port.");
  }

  public function login($username, $password)
  {
    if (!@ssh2_auth_password($this->connection, $username, $password))
      throw new \Exception("Could not authenticate with username $username");

    $this->sftp = @ssh2_sftp($this->connection);
    if (!$this->sftp)
      throw new \Exception("Could not initialize SFTP subsystem.");
  }

  public function loginKey(string $userName, string $pubKey, string $privKey, ?string $secret = null)
  {
    if (!@ssh2_auth_pubkey_file($this->connection, $userName, $pubKey, $privKey, $secret)) {
      throw new \Exception("Could not authenticate with username $userName and keys $pubKey/$privKey");
    }

    $this->sftp = @ssh2_sftp($this->connection);
    if (!$this->sftp) {
      throw new \Exception("Could not initialize SFTP subsystem.");
    }
  }

  function listFiles($remoteDir, $recursive = false)
  {
    $sftp = $this->sftp;
    $dir = "ssh2.sftp://$sftp$remoteDir";
    $tempArray = array();
    $handle = opendir($dir);
    if ($handle === false) {
      throw new \Exception("Could not open dir: $remoteDir");
    }
    // List all the files
    while (false !== ($file = readdir($handle))) {
      if (substr("$file", 0, 1) != ".") {
        if (is_dir("$dir/$file")) {
          if ($recursive) {
            $children = $this->listFiles("$remoteDir/$file", true);
            if (!empty($children)) {
              $tempArray = array_merge($tempArray, $children);
            }
          }
        } else {
          if ($recursive) {
            $tempArray[] = "$remoteDir/$file";
          } else {
            $tempArray[] = $file;
          }
        }
      }
    }
    closedir($handle);
    return array_filter($tempArray);
  }

  public function uploadFile($localFile, $remoteFile)
  {
    \App\Log::warning("SftpConnection::uploadFile:$localFile:$remoteFile");
    $sftp = $this->sftp;
    $stream = @fopen("ssh2.sftp://$sftp$remoteFile", 'w');

    if (!$stream)
      throw new \Exception("Could not open file: $remoteFile");

    $data_to_send = @file_get_contents($localFile);
    if ($data_to_send === false)
      throw new \Exception("Could not open local file: $localFile.");

    if (@fwrite($stream, $data_to_send) === false)
      throw new \Exception("Could not send data from file: $localFile.");

    @fclose($stream);
  }

  public function downloadFile($remoteFile, $localFile)
  {
    \App\Log::warning("SftpConnection::downloadFile:$remoteFile:$localFile");
    $sftp = $this->sftp;
    $stream = @fopen("ssh2.sftp://$sftp$remoteFile", 'r');

    if (!$stream)
      throw new \Exception("Could not open file: $remoteFile");

    $data = @stream_get_contents($stream);
    if ($data === false)
      throw new \Exception("Could not read remote file: $remoteFile.");

    if (@file_put_contents($localFile, $data) === false)
      throw new \Exception("Could not save data to file: $localFile.");

    @fclose($stream);
  }

  public function mkdir($remoteDir)
  {
    \App\Log::warning("SftpConnection::mkdir:$remoteDir");
    $sftp = $this->sftp;
    $dir = "ssh2.sftp://$sftp$remoteDir";
    if (!file_exists($dir)) {
      mkdir($dir, 0770, true);
    } else if (is_file($dir)) {
      throw new \Exception("$dir is a file");
    }
  }

  public function deleteFile($remoteFile)
  {
    $sftp = $this->sftp;
    unlink("ssh2.sftp://$sftp$remoteFile");
  }
}
