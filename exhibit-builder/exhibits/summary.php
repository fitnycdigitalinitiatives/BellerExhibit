<?php echo head(array('title' => metadata('exhibit', 'title'), 'bodyclass'=>'exhibits summary')); ?>
  <div class="row align-items-center fullscreen" id="exhibit-landing">
    <div class="col-md-7 py-5 ml-md-4" id="summary-block">
      <div class="jumbotron bg-light rounded-0 mb-0">
        <h1>Max Meyer and A. Beller & Co.</h1>
        <h2>Interpreting a Hidden History of NYC's Garment District</h2>
        <?php if ($exhibitDescription = metadata('exhibit', 'description', array('no_escape' => true))): ?>
  				<div class="exhibit-description">
  					<?php echo $exhibitDescription; ?>
  				</div>
  			<?php endif; ?>
        <?php $firstPage = $exhibit->getFirstTopPage(); ?>
        <?php echo exhibit_builder_link_to_exhibit($exhibit, '<i class="fas fa-angle-right" aria-hidden="true"></i><span class="sr-only">begin</span>', array('class' => 'begin text-dark'), $firstPage); ?>
      </div>
    </div>
  </div>


<?php echo foot(); ?>
