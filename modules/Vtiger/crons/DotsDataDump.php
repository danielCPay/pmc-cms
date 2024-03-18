<?php

/**
 * Dumps data for PowerBI.
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Vtiger_DotsDataDump_Cron class.
 */
class Vtiger_DotsDataDump_Cron extends \App\CronHandler
{
  const SERVICE_NAME = 'LBL_DOTS_DATA_DUMP_HANDLER';

  /**
   * {@inheritdoc}
   */
  public function process()
  {
    if (\App\Request::_get('service') === self::SERVICE_NAME) {
      $outVolume = \App\Config::datadump('outVolume');
      $outDir = \App\Config::datadump('outDir');
      $siteUrl = \App\Config::datadump('siteUrl');
      $dumpPort = \App\Config::datadump('dumpPort');
      $tables = \App\Config::datadump('tables');

      \App\Log::warning("Vtiger::cron::Vtiger_DotsDataDump_Cron:" . var_export([
        'outVolume' => $outVolume, 'outDir' => $outDir, 'siteUrl' => $siteUrl, 
        'dumpPort' => $dumpPort, 'tables' => $tables
        ], true));

      foreach ($tables as $table) {
        \App\Log::warning("Vtiger::cron::Vtiger_DotsDataDump_Cron: Exporting $table");

        $columns = [];
        $dataReader = \App\Db::getInstance()->createCommand("select column_name, data_type from information_schema.columns where table_schema = 'yetiforce' and table_name = '$table' order by ordinal_position")->query();
        while ($row = $dataReader->read()) {
          switch ($row['data_type']) {
            case 'tinyint':
            case 'int':
              $type = 'Int64.Type';
              break;
            case 'decimal':
              $type = 'type number';
              break;
            case 'date':
              $type = 'type date';
              break;
            case 'datetime':
              $type = 'type datetime';
              break;
            case 'text':
            case 'varchar':
              $type = 'type text';
              break;
            default:
              throw new \Exception('Unexpected data type ' . $row['data_type'] . ' for column ' . $row['column_name']);
          }
          $columns[] = '{"' . $row['column_name'] . '", ' . $type . '}';
        }
        $columns = implode(', ', $columns);
        $meta = <<<META
let
  Source = Csv.Document(Web.Contents("$siteUrl:$dumpPort/$table.csv"),[Delimiter=",", Encoding=65001, QuoteStyle=QuoteStyle.None]),
  #"Promoted Headers" = Table.PromoteHeaders(Source, [PromoteAllScalars=true]),
  #"Changed Types" = Table.TransformColumnTypes(#"Promoted Headers",{{$columns}}, "en-US"),
  #"Renamed Columns" = Table.RenameColumns(#"Changed Types", {})
in
  #"Renamed Columns"

META;

        file_put_contents("$outVolume$outDir$table.meta", $meta);

        $file = fopen("$outVolume$outDir$table.csv", 'w');
        if ($file !== false) {
          $dataReader = \App\Db::getInstance()->createCommand('select * from ' . $table)->query();
          $headerExported = false;
          while ($row = $dataReader->read()) {
            if (!$headerExported) {
              fputcsv($file, array_keys($row));
              $headerExported = true;
            }
            foreach ($row as $key => $value) {
              if (is_string($value) && str_contains($value, "\n")) {
                $row[$key] = str_replace("\n", '; ', $value);
              }
            }
            fputcsv($file, $row, ',', '"');
          }
          fclose($file);
        }
      }

      \App\Log::warning("Vtiger::cron::Vtiger_DotsDataDump_Cron: Finished");
    }
  }
}
