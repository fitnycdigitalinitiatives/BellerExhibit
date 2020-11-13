<?php
echo head(array(
    'title' => metadata('exhibit_page', 'title') . ' &middot; ' . metadata('exhibit', 'title'),
    'bodyclass' => 'exhibits show'));
?>
  <nav class="navbar navbar-light bg-light">
    <?php echo exhibit_builder_link_to_exhibit(null, 'Max Meyer', array('class' => 'navbar-brand text-muted')); ?>
    <?php echo link_to_home_page('<i class="fas fa-times" aria-hidden="true"></i><span class="sr-only">Close exhibit</span>', array('class' => 'text-muted exhibit-close', 'data-barba-prevent' => "self")); ?>
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
