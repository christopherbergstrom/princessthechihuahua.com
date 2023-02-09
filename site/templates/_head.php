<?php $form = $forms->render('well-wishes'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Princess the Chihuahua</title>
  <?php echo $form->styles; ?>
  <?php echo $form->scripts; ?>
  <link rel="stylesheet" href="<?= $config->urls->templates ?>css/uikit.min.css" >
  <link rel="stylesheet" href="<?= $config->urls->templates ?>css/main-1.css" >
  <script src="<?= $config->urls->templates ?>js/uikit.min.js"></script>
  <script src="<?= $config->urls->templates ?>js/uikit-icons.min.js"></script>
  <script src="<?= $config->urls->templates ?>js/main.js"></script>
</head>
<body>
  <?php include "_loader.php"; ?>
  <main>