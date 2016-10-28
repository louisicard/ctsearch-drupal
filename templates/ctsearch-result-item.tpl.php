<div class="ctsearch-result-item">
  <div class="item-id"><strong>Item ID</strong> = <?php print $item['_id']; ?></div>
  <div class="item-score"><strong>Item score</strong> = <?php print $item['_score']; ?></div>
  <div class="item-source-title"><strong>Source</strong> :</div>
  <div class="item-source">
    <?php foreach ($item['_source'] as $k => $val): ?>
      <div class="source-key"><?php print $k; ?></div>
      <div class="source-value">
        <?php if (!is_array($val)): ?>
          <?php print $val; ?>
        <?php else: ?>
          <?php print implode(', ', $val); ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>