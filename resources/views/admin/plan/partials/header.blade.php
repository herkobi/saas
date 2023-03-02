<h3 class="section-title mb-0">{{ $title }}</h3>
<?php if(!empty($link)) : ?>
<a href="{{ route($link) }}" title="{{ $linktext }}" class="btn btn-sm btn-primary add-btn shadow-none rounded-0"><i class="ri-add-line"></i> {{ $linktext }}</a>
<?php endif; ?>
