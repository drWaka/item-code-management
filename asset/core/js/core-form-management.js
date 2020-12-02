function initialize_form_validation (formContainer) {
  // Modal Node
  let formContainerNode = document.querySelector(formContainer);

  // The Form inside the modal
  let form = formContainerNode.querySelector('form');
  let formName = form.getAttribute('form-name');

  // The Submit Button inside the modal
  let submitBtn = formContainerNode.querySelector('.submit-btn');
  submitBtn.addEventListener('click', function () {
    validate_form(form);
  });

  // The Form-Controls inside the form
  let dataField = form.querySelectorAll('.form-control');
  console.log(dataField);

  // Condition that will determine on what are the field that must be used everytime the function is invoked
  if (formName == 'login-form') {
    add_input_event(dataField[0], 'User ID', 'text-int', true, form);
    add_input_event(dataField[1], 'Password', 'password', true, form);
  } else if (formName == 'account-recovery') {
    add_input_event(dataField[0], 'User ID', 'text-int', true, form);
  } else if (formName == 'account-recovery-2') {
    add_input_event(dataField[0], 'Recovery Code', 'text-int', true, form);
  } else if (formName == 'account-recovery-3') {
    add_input_event(dataField[0], 'New Password', 'text-int', true, form);
    add_input_event(dataField[1], 'New Password', 'text-int', true, form);
  } else if (formName == 'guide-mgmt-form') {
    add_input_event(dataField[0], 'Guide Title', 'text-int', true, form);
    add_input_event(dataField[1], 'Guide Description', 'text-int', true, form);
  } else if (formName == 'guide-mgmt-hmo-form') {
    add_input_event(dataField[0], 'HMO Name', 'text-int', true, form);

    var recordId = form.querySelector('[name="recordId"]');
    recordId = recordId.value;
    $isRequired = recordId == 'new-rec' ? true : false;
    add_input_event(dataField[1], 'HMO Image', 'text-int', $isRequired, form);
  } 
}

function validate_form(form) {
  // let update_form = form;
  let inputField = form.querySelectorAll('.form-control');
  let formName = form.getAttribute('form-name');
  var formValidity = true;

  if (formName == 'login-form') {
    formValidity = final_data_check(inputField[0], 'User ID', 'text-int', true, formValidity);
    formValidity = final_data_check(inputField[1], 'Password', 'password', true, formValidity);
  } else if (formName == 'account-recovery') {
    formValidity = final_data_check(inputField[0], 'User ID', 'text-int', true, formValidity);
  } else if (formName == 'account-recovery-2') {
    formValidity = final_data_check(inputField[0], 'Recovery Code', 'text-int', true, formValidity);
  } else if (formName == 'account-recovery-3') {
    formValidity = final_data_check(inputField[0], 'New Password', 'text-int', true, formValidity);
    formValidity = final_data_check(inputField[1], 'New Password', 'text-int', true, formValidity);
  } else if (formName == 'guide-mgmt-form') {
    formValidity = final_data_check(inputField[0], 'Guide Title', 'text-int', true, formValidity);
    formValidity = final_data_check(inputField[1], 'Guide Description', 'text-int', true, formValidity);
  } else if (formName == 'guide-mgmt-hmo-form') {
    formValidity = final_data_check(inputField[0], 'HMO Name', 'text-int', true, formValidity);

    var recordId = form.querySelector('[name="recordId"]');
    recordId = recordId.value;
    $isRequired = recordId == 'new-rec' ? true : false;
    formValidity = final_data_check(inputField[1], 'HMO Image', 'text-int', $isRequired, formValidity);
  } 

  if (formValidity == true) {
    let submitType = form.getAttribute('submit-type');
    if (submitType == 'synchronous') {
      form.submit();
    } else {
      let link = form.getAttribute('action');
      let dataFieldValue = new FormData(form);
      let contentType = 'file-image';

      send_request_asycn(link, 'POST', dataFieldValue, '.modal-container', 'modal-rec', contentType);
    }
  }
}

// Function that will give event listeners to input fields when called
function add_input_event(element, name, data_type, required, form) {
  // the event listenter that validates a particular elem
  element.addEventListener('blur', function() {
    // function that will validate the field according to its type
    var field_elem = check_data(element, name, data_type, required);
    if (field_elem.valid == 0) {
      // if the value of the field is invalid an error will be appended in the field's parent element
      add_error(field_elem);
    }
  });

  // element.addEventListener('keyup', function(e) {
  //   e.preventDefault();
  //   if (e.keyCode == 13) {
  //     validate_form(form);
  //   }
  // });
}

function final_data_check(element, name, data_type, required, form_validity) {
  var elem = check_data(element, name, data_type, required);
  if (elem.valid == 0) {
    add_error(elem);
    return false;
  } else {
    return form_validity;
  }
}

function add_error(obj) {
  var parent = obj.element.parentNode;

  if (parent.className.indexOf('has-error') == -1) {
    parent.classList.add('has-error');
    parent.classList.add('has-feedback');

    var error_icon = document.createElement('span');
    error_icon.classList.add('fa');
    error_icon.classList.add('fa-times');
    error_icon.classList.add('form-control-feedback');
    error_icon.classList.add('error-icon');
    parent.appendChild(error_icon);
    var error_msg = document.createElement('div');
    error_msg.textContent = obj.err_msg;
    error_msg.classList.add('error-text');
    parent.appendChild(error_msg);
  }
}

function remove_error(elem) {
  var parent = elem.parentNode;
  if(parent.className.indexOf('has-error') > -1) {
    parent.classList.remove('has-error');
    parent.classList.remove('has-feedback');
    var child = parent.childNodes;
    
    /*
     *
     * Commented Temporarily, Might be implemented in the future
     *
     */
    
    // while (child[3]) {
    //   parent.removeChild(child[3]);
    // }

    for (var index = 0 ; index < child.length ; index++) {
      // console.log(child[index]);
      let i = index;
      if (child[i].nodeType == 1) {
        if (child[i].className.indexOf('error') > -1) {
          console.log(child[i]);
          parent.removeChild(child[i]);
        }
      }
    }

    for (var index = 0 ; index < child.length ; index++) {
      // console.log(child[index]);
      let i = index;
      if (child[i].nodeType == 1) {
        if (child[i].className.indexOf('error') > -1) {
          console.log(child[i]);
          parent.removeChild(child[i]);
        }
      }
    }
  }
}

function check_data(elem, label, type, required) {
  // removes the previous error to prevent error redundancy
  remove_error(elem);
  var response = {
    valid : 1,
    err_msg : '',
    element : elem
  };

  function required_func() {

    if (elem.value == '') {
      response.valid = 0;
      response.err_msg = 'Please enter a ' + label;
    } else {
      response.valid = 1;
    }
  }
  if (type == 'text') {
    var value = elem.value.split("");
    for (var index = 0 ; index < value.length ; index++) {
      if (!isNaN(value[index]) && value[index] != ' ') {
        response.valid = 0;
        response.err_msg = label + ' must not contain numbers';
        break;
      }
    }
    if (response.valid != 0) {
      if (required == true) {
        required_func();
      } else {
        response.valid = 1;
      }
    }
  } else if (type == 'int') {
    if (isNaN(elem.value)) {
      response.valid = 0;
      response.err_msg = 'Please enter a valid ' + label;
    } else {
      if (response.valid !== 0) {
        if (required == true) {
          required_func();
        } else {
          response.valid = 1;
        }
      }
    }
  } else if (type == 'double') {
    var values = elem.value.split(".");
    for (var index = 0 ; index < values.length ; index++) {
      (function() {
        if (isNaN(values[index])) {
          response.valid = 0;
          response.err_msg = 'Please enter a valid ' + label;
        }
      })();
    }

    if (response.valid !== 0) {
      if (required == true) {
        required_func();
      } else {
        response.valid = 1;
      }
    }
  } else if (type == 'text-int' || type == 'password') {
    if (required == true) {
      required_func();
    } else {
      response.valid = 1;
    }
  } else if (type == 'date') {
    // The value of the input
    var value = elem.value.split("-");

    // assigned the user input into a variable for comparison purposes
    var date_input = new Date();
    date_input.setFullYear(value[0], value[1] - 1, value[2]);

    // assigned the date last 18 years ago which will be our minimum year allowed
    var date_min = new Date();
    date_min.setFullYear(date_min.getFullYear() - 18, date_min.getMonth(), date_min.getDate());
    // console.log(date_min);
    // console.log(value);
    if (date_input > date_min) {
      if (label == 'Date of Birth') {
        response.valid = 0;
        response.err_msg = label + ' must be atleast earlier than 18 years';
      }
    } else if (required == true) {
      required_func();
    }
  } else if (type == 'email') {
    if (required == true) {
      required_func();
    }
  } else {
    type = type.split('-');

    if (type[0] == 'file') {
      if (type[1] == 'doc') {
        var value = elem.value.split('.');
        if (value[value.length - 1] != 'doc' && value[value.length - 1] != 'docx' && value[value.length - 1] != 'pdf') {
          response.valid = 0;
          response.err_msg = 'Please choose a valid ' + label;
        } else {
          if (required == true) {
            required_func();
          } else {
            response.valid = 1;
          }
        }
      }
    }
  }

  // Create a code that will validate a date
  return response;
}

// Event Listener for Synchronous Forms
$('[submit-type="synchronous"]').on('keyup', '.form-control', function(e) {
  if (e.keyCode == 13) {
    var formNode = ($(this))[0];
    while (formNode.tagName.toLowerCase() !== 'form') {
      formNode = formNode.parentNode;
    }

    validate_form(formNode);
  }
});