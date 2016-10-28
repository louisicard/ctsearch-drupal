<div class="search-summary">
  <?php print t('%count result(s) for your search', array('%count' => $total)); ?>
</div>
<div class="ctsearch-result-list">
  <?php if (count($items) <= 5 && isset($did_you_mean) && $did_you_mean != null): ?>
    <div class="did-you-mean">
      <?php $link = url(current_path(), array('query' => array('query' => $did_you_mean))); ?>
      <?php print t('Did you mean <em><a href="@link">@did_you_mean</a></em>?', array('@link' => $link, '@did_you_mean' => $did_you_mean)); ?>
    </div>
  <?php endif; ?>
  <?php foreach ($items as $item): ?>
    <?php print $item; ?>
  <?php endforeach; ?>
</div>