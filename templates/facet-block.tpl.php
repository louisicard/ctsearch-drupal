<div class="ctsearch-facet" id="ctsearch-facet-<?php print str_replace('.', '_', $facet_id); ?>">
  <?php if(isset($facet['buckets']) && count($facet['buckets']) > 0):?>
  <ul>
    <?php foreach ($facet['buckets'] as $bucket): ?>
      <?php if (!isset($bucket['remove_filter_url'])): ?>
        <li><a href="<?php print $bucket['filter_url'];?>" title="<?php print htmlentities($bucket['key']);?>"><?php print $bucket['key'];?> (<?php print $bucket['doc_count'];?>)</a></li>
      <?php else: ?>
        <li class="active"><?php print $bucket['key'];?> (<?php print $bucket['doc_count'];?>) <a href="<?php print $bucket['remove_filter_url'];?>" class="remove-link" title="<?php print t('Remove');?>">X</a></li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
  <?php endif;?>
  <?php if(isset($facet['see_more_url'])):?>
  <div class="see-more-link">
    <a href="<?php print $facet['see_more_url'];?>" class="see-more" title="<?php print t('See more');?>"><?php print t('See more');?></a>
  </div>
  <?php endif;?>
</div>