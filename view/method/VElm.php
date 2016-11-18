<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 28 October 2016, 2:28 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace view\method;
include_once $_SERVER['DOCUMENT_ROOT'].'/model/view/MView.php';

use model\view\MView;

class VElm extends MView
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function display()
    {
?>
        <!doctype html>
        <html class="no-js" lang="">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="x-ua-compatible" content="ie=edge">
            <title></title>
            <meta name="description" content="">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/normalize.css') ?>">
            <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/main.css') ?>">
            <script src="<?php echo base_url('assets/frontend/js/modernizr-2.8.3.min.js') ?>"></script>
        </head>
        <body>
        <?php
            $tableWidth =  100 * ceil($this->data['feature']/5);
            $columnWidth = (100 / ($this->data['feature'] + 1));
        ?>

        <h2>Data</h2>
        <table style="width: <?php echo $tableWidth?>%" border="solid 1px">
            <tr>
                <?php
                $feature = $this->data['feature'];
                for ($i = 0, $is = $feature; ++$i <= $is;)
                {
                    echo "<th style=\" width: {$columnWidth}\"%>f{$i}</th>";
                }
                echo "<th style=\" width: {$columnWidth}\"%>value</th>";
                unset($feature);
                ?>
            </tr>
            <?php
            $feature = $this->data['feature'];
            $totalData = $this->data['total'] - $feature;
            foreach ($this->data['metadata']['type'] as $type)
            {
                echo '<tr>';
                echo '<td colspan="'.($this->data['metadata']['total']['parameter'] + 1).'" align="center">';
                echo ucfirst($type);
                echo '</td>';
                echo '</tr>';

                foreach ($this->data['data']['data'][$type] as $key => $value)
                {
                    echo '<tr>';
                    foreach ($value['data'] as $dVal)
                    {
                        echo sprintf("%s%.3f%s", "<td style=\" width: {$columnWidth}\"%>", $dVal, '</td>' );
                    }
                    echo sprintf("%s%.3f%s", "<td style=\" width: {$columnWidth}\"%>", $value['class'], '</td>' );
                }
            }
            unset($feature, $totalData);
            ?>
        </table>

        <h2>Normalisasi</h2>
        <table style="width: <?php echo $tableWidth?>%" border="solid 1px">
        <tr>
                <?php
                $feature = $this->data['feature'];
                for ($i = 0, $is = $feature; ++$i <= $is;)
                {
                    echo "<th style=\" width: {$columnWidth}%\">f{$i}</th>";
                }
                echo "<th style=\" width: {$columnWidth}%\">value</th>";
                unset($feature);
                ?>
            </tr>
            <?php
            $feature = $this->data['feature'];
            $totalData = $this->data['total'] - $feature;
            foreach ($this->data['metadata']['type'] as $type)
            {
                echo '<tr>';
                echo '<td colspan="' . ($this->data['metadata']['total']['parameter'] + 1) . '" align="center">';
                echo ucfirst($type);
                echo '</td>';
                echo '</tr>';
                foreach ($this->data['data']['minmax'][$type] as $key => $value)
                {
                    echo '<tr>';
                    foreach ($value['data'] as $dVal)
                    {
                        echo sprintf("%s%.3f%s", "<td style=\" width: {$columnWidth}\"%>", $dVal, '</td>');
                    }
                    echo sprintf("%s%.3f%s", "<td style=\" width: {$columnWidth}\"%>", $value['class'], '</td>');
                    echo '</tr>';
                }
            }
            unset($feature, $totalData);
            ?>
        </table>

        <script src="<?php echo base_url('assets/frontend/bower_components/jquery/dist/jquery.min.js') ?>"></script>
        <script src="<?php echo base_url('assets/frontend/js/plugins.js') ?>"></script>
        <script src="<?php echo base_url('assets/frontend/js/main.js') ?>"></script>
        </body>
        </html>

        <?php
    }
}