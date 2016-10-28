<div class="ctsearch-sort">
  <ul>
    <?php foreach ($sortable as $label => $value): ?>
      <?php if ($value['active']): ?>
        <li class="active"><?php print $label; ?></li>
      <?php else: ?>
        <li><a href="<?php print $value['link']; ?>" title="<?php print htmlentities($label); ?>"><?php print $label; ?></a></li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</div>