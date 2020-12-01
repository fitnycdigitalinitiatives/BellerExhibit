<?php echo head(array('title' => metadata('exhibit', 'title'), 'bodyclass'=>'exhibits summary')); ?>
  <div class="row align-items-center fullscreen" id="exhibit-landing">
    <div class="col-md-9 col-lg-7 col-xl-8 py-5 ml-md-4" id="summary-block">
      <div class="jumbotron bg-light rounded-0 mb-0">
        <h1>Max Meyer and
          <br>A. Beller & Co.</h1>
        <h2>Interpreting a Hidden History of NYC's Garment District</h2>
        <ul class="list-inline">
          <?php $toppages = $exhibit->TopPages; ?>
          <?php foreach ($toppages as $page): ?>
            <li class="list-inline-item">
              <a class="list-inline-link text-dark" href="<?php echo $page->getRecordUrl('show'); ?>"><?php echo $page->title; ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php $firstPage = $exhibit->getFirstTopPage(); ?>
        <?php echo exhibit_builder_link_to_exhibit($exhibit, '<i class="fas fa-angle-right" aria-hidden="true"></i><span class="sr-only">begin</span>', array('class' => 'begin text-dark'), $firstPage); ?>
        <a href="https://www.fitnyc.edu">
          <img id="fit-logo" src="<?php echo img('wordmark-black-1-line.png') ?>" alt="FIT SUNY Logo" id="fit-logo">
        </a>
      </div>
    </div>
  </div>


<?php echo foot(); ?>
