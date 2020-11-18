<?php
echo head(array(
    'title' => metadata('exhibit_page', 'title') . ' &middot; ' . metadata('exhibit', 'title'),
    'bodyclass' => 'exhibits show'));
?>
  <nav class="navbar navbar-light bg-light">
    <?php echo exhibit_builder_link_to_exhibit(null, 'Max Meyer', array('class' => 'navbar-brand text-muted')); ?>
    <button class="exhibit-menu text-muted border-0 bg-transparent" type="button" data-toggle="offcanvas" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fas fa-bars" aria-hidden="true"></i><span class="sr-only">Toggle navigation menu</span>
    </button>
    <div class="offcanvas-collapse navbar-collapse" id="navbarSupportedContent">
    <button class="exhibit-menu border-0 bg-transparent text-dark" type="button" data-toggle="offcanvas" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fas fa-times" aria-hidden="true"></i><span class="sr-only">Toggle navigation menu</span>
    </button>
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <?php echo exhibit_builder_link_to_exhibit($exhibit, 'Home', array('class' => 'nav-link text-dark')); ?>
      </li>
      <?php $toppages = $exhibit->TopPages; ?>
      <?php foreach ($toppages as $page): ?>
        <li class="nav-item">
          <a class="nav-link text-dark" href="<?php echo $page->getRecordUrl('show'); ?>"><?php echo $page->title; ?></a>
        </li>
      <?php endforeach; ?>
      <li class="nav-item">
        <?php echo link_to_home_page('Leave Exhibit', array('class' => 'nav-link text-dark', 'data-barba-prevent' => "self")); ?>
      </li>
    </ul>
  </div>
</nav>
  </nav>

    <?php exhibit_builder_render_exhibit_page(); ?>

    <?php if ($prevLink = exhibit_builder_link_to_previous_page('<i class="fas fa-angle-left" aria-hidden="true"></i><span class="sr-only">Previous</span>', array('class' => 'text-dark previous'))): ?>
      <?php echo $prevLink; ?>
    <?php else: ?>
      <?php echo exhibit_builder_link_to_exhibit(null, '<i class="fas fa-angle-left" aria-hidden="true"></i><span class="sr-only">Previous</span>', array('class' => 'text-dark previous')); ?>
    <?php endif; ?>
    <?php if ($nextLink = exhibit_builder_link_to_next_page('<i class="fas fa-angle-right" aria-hidden="true"></i><span class="sr-only">Next</span>', array('class' => 'text-dark next float-right float-md-none'))): ?>
      <?php echo $nextLink; ?>
    <?php endif; ?>

<?php echo foot(); ?>
