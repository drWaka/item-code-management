// Function for the number refresh at the queue board
function send_request_asycn (url, method, data, container, transName, content_type = 'application/x-www-form-urlencoded') {
  var ajax_paramemter = {
    url : url,
    method : method,
    data : data,
    success : function(result) {
      console.log(result);
      
      result = JSON.parse(result);
      console.log(result);
      
      if (result[0] == 'success') {

        if (transName.indexOf('recordList') > -1) {
          if (pageProp['isMultiRec'] == 1) {
            let metaType = transName.split('-');
            pageProp['curPage'][metaType[1]] = 1;
            pageProp['recObj'][metaType[1]] = result[1].recContent;
            pageProp['totalRec'][metaType[1]] = result[1].totalRec;
            changePage(1, container, metaType[1]);
          } else {
            pageProp['curPage'] = 1;
            pageProp['recObj'] = result[1].recContent;
            pageProp['totalRec'] = result[1].totalRec;
            changePage(1, container);
          } 
        } else if (transName == 'modal-rec') {
          // Remove any existing backdrop
          var extraBackDrop = document.querySelectorAll('.modal-backdrop');
          for (var index = 0 ; index < extraBackDrop.length ; index++) {
            (function() {
              let i = index;
              extraBackDrop[i].parentNode.removeChild(extraBackDrop[i]);
            })();
          }

          // Initialize Modal
          $('.modal-container').html(result[1].modal);
          $('#transaction-modal').attr("data-backdrop", "static");
          $('#transaction-modal').attr("data-keyboard", "false");
          $('#transaction-modal').modal('show');
          
          // The Flag the will determine if the modal has a form element 
          // Which will be used for the data validation
          let modalForm = document.querySelector('#transaction-modal form');
          if (modalForm != null && modalForm != undefined) {
            initialize_form_validation('#transaction-modal');
          }
        } else if (transName == 'dynamic-field') {
          let fieldNode = document.querySelector("[name='" + container + "']");
          $(fieldNode).html(result[1].content);
        } else if (transName == 'choose-field') {
          var fieldNode = document.querySelector("[name='" + container + "']");
          var childNode = fieldNode.querySelectorAll('option');

          for (var index = 0 ; index < childNode.length ; index++) {
            (function() {
              var curNodeVal = childNode[index].getAttribute('value');
              if (curNodeVal.toLowerCase() == result[1].selectedIndex.toLowerCase()) {
                fieldNode.selectedIndex = index;
              }
            })();
          }
          console.log(fieldNode);
        }
        
      } else {
        // Transaction Failed
        $('.modal-container').html(result[1].modal);
        $('#transaction-modal').attr("data-backdrop", "static");
        $('#transaction-modal').attr("data-keyboard", "false");
        $('#transaction-modal').modal('show');
      }
    },
    error : function () {
      console.log('Asynchronous Request Failed');
    }
  }

  // console.log(ajax_paramemter);
  if (content_type == 'file-image') {
    ajax_paramemter.cache       = false;
    ajax_paramemter.processData = false;  
    ajax_paramemter.contentType = false;
  } else {
    ajax_paramemter.contentType = content_type;
  }

  // Send AJAX Request
  $.ajax(ajax_paramemter);
}


// Update Currently Fetched Record Event Listener
$(document).on('click', '.transaction-btn', function() {
  let container = '.' + $(this).attr('data-target');
  let link = $(this).attr('data-link');
  let transName = $(this).attr('trans-name');
  let content = JSON.parse($(this).attr('data-content'));
  
  send_request_asycn (link, 'POST', content, container, transName);
});

// Event Trigger that will reset all of the filtering options at the page
$(document).on('click', '.update-rec-content', function() {
  var filterElem = document.querySelectorAll('.filter-cotainer .form-control');

  for (var index = 0 ; index < filterElem.length ; index++) {
    (function() {
      let tagName = filterElem[index].tagName.toLowerCase();
      console.log(tagName);
      if (tagName == 'select') {
        filterElem[index].selectedIndex = 0;
      } else if (tagName == 'input') {
        filterElem[index].value = '';
      }
    })();
  }
});

// Event Trigger for custom table layout record
$(document).on('click', '.btn-trigger', function() {
  // Transaction Success Trigger
  refreshList();
});