<!-- pages -->

<?php 
$pageData = getActivePages();
?>
@if(!__isEmpty(getActivePages()))
@foreach($pageData as $pageKey => $pageValue)
<li class="nav-item">
<a class="nav-link me-lg-3" href="{{ route('page.preview', [
    'pageUId' => $pageValue['_uid'],
    'slug' => slugIt($pageValue['slug']),
    ])}}"> {{  __tr($pageValue['title']) }}</a></li>
@endforeach
  @endif
   <!-- /pages -->