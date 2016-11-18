<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 28 October 2016, 4:03 AM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

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


<script src="
<?php echo base_url('assets/frontend/bower_components/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?php echo base_url('assets/frontend/js/plugins.js') ?>"></script>
<script src="<?php echo base_url('assets/frontend/js/main.js') ?>"></script>
<script src="<?php echo base_url('assets/frontend/bower_components/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/frontend/bower_components/mmoment-timezone/builds/moment-timezone.min.js') ?>"></script>
<script type="text/javascript">
    function AjaxSendForm(url, method, data)
    {
        return $.ajax({
            type: method,
            url: url,
            data: data,
            dataType: 'json'
        });
    }
</script>
<script type="text/javascript">
    function doCall(maxDate, currentDate, base, to)
    {
        var currentDateFormatted = currentDate.format("YYYY-MM-DD");
        $.when(AjaxSendForm('http://api.fixer.io/' + currentDateFormatted + '?symbols='+to+'&base='+base, 'GET', {}))
            .done(function (a1)
            {
                document.write('Success Retrive {' +base+'->'+to+'} from fixer for : '+ currentDateFormatted +'<br>');
                $.when(AjaxSendForm("http://lide-app.com.dev/ws/currency/add", 'POST', {
                    base: base.toLowerCase(),
                    to: to.toLowerCase(),
                    date: currentDateFormatted,
                    value: a1['rates'][to]
                }))
                    .done(function ()
                    {
                        document.write('Success Store {' +base+'->'+to+'} to database for : '+ currentDateFormatted +'<br>');
                        if (!currentDate.isSame(maxDate))
                        {
                            doCall(maxDate, currentDate.subtract(1, 'days'), base, to);
                        }
                    });
            });
    }

    //doCall(moment('2016-10-27').subtract(4 * 356, 'days'), moment('2016-10-27'), 'USD', 'IDR');
</script>
</body>
</html>

