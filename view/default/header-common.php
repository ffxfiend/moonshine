<html lang="en">
<head>
    <title><?= $meta['title'] ?></title>
    <meta name="keywords" content="<?= $meta['keywords'] ?>" />
    <meta name="description" content="<?= $meta['description'] ?>" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />


    <link rel="stylesheet" type="text/css" href="<?= ms_themePath() ?>/css/master.css" />

    <?php 
    if (isset($additional_css)) {
    	echo $additional_css;
    } 
    ?>

</head>
<body>