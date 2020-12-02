function changePage(page, container, metaType = '') {
  // Empty the Container
  $(container).html('');
  
  for (var index = (page - 1) * pageProp['pageLimit'] ; index < (page * pageProp['pageLimit']) ; index++) {
    (function() {
      if (pageProp['isMultiRec'] == 1) {
        $(container).append(pageProp['recObj'][metaType][index]);
      } else {
        $(container).append(pageProp['recObj'][index]);
      }
    })();
  }

  // Pagination Controls
  var pageCont = document.querySelector(container);
  var pageContTemp = null;
  while (pageContTemp == null) {
    pageContTemp = pageCont.querySelector('.pagination-container');
    if (pageContTemp !== null) {
      pageCont = pageContTemp;
    } else {
      pageCont = pageCont.parentNode;
    }
  }
  var prevBtn = pageCont.querySelector('.prev-btn');
  var nextBtn = pageCont.querySelector('.next-btn');
  
  // Prev Page Management
  if (page <= 1) {
    prevBtn.setAttribute('disabled', 'disabled');
  } else {
    prevBtn.removeAttribute('disabled');
  }

  // Next Page Management
  $pageCiel = pageProp['isMultiRec'] == 1 
    ? Math.ceil(pageProp['totalRec'][metaType] / pageProp['pageLimit'])
    : Math.ceil(pageProp['totalRec'] / pageProp['pageLimit']);

  if (page == $pageCiel) {
    nextBtn.setAttribute('disabled', 'disabled');
  } else {
    nextBtn.removeAttribute('disabled');
  }  
}

$(document).ready(function() {
  // Pagination Button Events
  $('.prev-btn').on('click', function() {
    let container = '.' + $(this).attr('data-container');

    if (pageProp['isMultiRec'] == 1) {
      // Pagination for Multi Record Pages
      let metaType = $(this).attr('meta-type');

      // Validation
      pageProp['curPage'][metaType] = pageProp['curPage'][metaType] <= 1
        ? 1
        : pageProp['curPage'][metaType] - 1;

      // Update Content
      changePage(pageProp['curPage'][metaType], container, metaType);
    } else {
      // Validation
      pageProp['curPage'] = pageProp['curPage'] <= 1
        ? 1
        : pageProp['curPage'] - 1;

      // Pagination for Single Record Pages
      changePage(pageProp['curPage'], container);
    }
  });

  $('.next-btn').on('click', function() {
    let container = '.' + $(this).attr('data-container');

    if (pageProp['isMultiRec'] == 1) {
      // Pagination for Multi Record Pages
      let metaType = $(this).attr('meta-type');

      // Validation
      pageProp['curPage'][metaType] = pageProp['curPage'][metaType] >= Math.ceil(pageProp['totalRec'][metaType] / pageProp['pageLimit'])
        ? Math.ceil(pageProp['totalRec'][metaType] / pageProp['pageLimit'])
        : pageProp['curPage'][metaType] + 1;

      // Update Content
      changePage(pageProp['curPage'][metaType], container, metaType);
    } else {
      // Validation
      pageProp['curPage'] = pageProp['curPage'] >= Math.ceil(pageProp['totalRec'] / pageProp['pageLimit'])
        ? Math.ceil(pageProp['totalRec'] / pageProp['pageLimit'])
        : pageProp['curPage'] + 1;

      // Pagination for Single Record Pages
      changePage(pageProp['curPage'], container);
    }
  });
});