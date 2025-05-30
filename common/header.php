<!doctype html>
<html lang="en">

<head>
  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-107096384-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-107096384-1');
  </script>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Will build the page <title> -->
  <?php
  if (isset($title)) {
    $titleParts[] = strip_formatting($title);
  }
  $titleParts[] = option('site_title');
  ?>
  <title><?php echo implode(' &middot; ', $titleParts); ?></title>

  <!-- Will fire plugins that need to include their own files in <head> -->
  <?php fire_plugin_hook('public_head', array('view' => $this)); ?>
  <!-- Icon -->
  <link rel="icon" href="<?php echo img('favicon.ico'); ?>" type="image/x-icon">
  <link rel="shortcut icon" href="<?php echo img('favicon.ico'); ?>" type="image/x-icon">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
  <script src="https://kit.fontawesome.com/1ddf8635da.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="<?php echo src('owl.carousel.min', 'javascripts/owl', 'css'); ?>">
  <link rel="stylesheet" href="<?php echo src('style', 'css', 'css', '1.1'); ?>">
</head>
<?php echo body_tag(array('id' => @$bodyid, 'class' => @$bodyclass, 'data-barba' => 'wrapper')); ?>
<a class="sr-only sr-only-focusable" href="#main">Skip to main content</a>
<div class="container-fluid" id="main" data-barba="container" data-barba-namespace="<?php echo @$bodyclass; ?>">