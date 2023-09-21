jQuery(document).ready(function (jQuery) {

  var productHashJson = null;

  jQuery(".auto_theme_update_switch").change(function () {
    var theme_slug_capture = jQuery(this).data("id");
    var theme_switch_is_checked = true;
    if (jQuery(this).prop("checked") == true) {
      theme_switch_is_checked = true;
    } else {
      theme_switch_is_checked = false;
    }

    var ajax_url = plugin_ajax_object.ajax_url;
    jQuery.ajax({
      data: {
        action: "fv_themes_autoupdate_switch",
        theme_slug_capture: theme_slug_capture,
        theme_switch_is_checked: theme_switch_is_checked,
      },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);
        if (json.status == "limitcrossed") {
          jQuery.alert({
            title: "Your auto update limit is crossed!",
            content:
              "You have already used all of your auto updates for this month. You can change auto update list again on next renewal. Your auto update limit is " +
              json.plan_limit +
              ". This is not applicable for ONETIME license.",
          });
        }
      },
    });
  });

  jQuery(".auto_plugin_update_switch").change(function () {
    var plugin_slug_capture = jQuery(this).data("id");
    var plugin_switch_is_checked = false;
    if (jQuery(this).prop("checked") == true) {
      plugin_switch_is_checked = true;
    } else {
      plugin_switch_is_checked = false;
    }

    var ajax_url = plugin_ajax_object.ajax_url;
    jQuery.ajax({
      data: {
        action: "fv_plugin_autoupdate_switch",
        plugin_slug_capture: plugin_slug_capture,
        plugin_switch_is_checked: plugin_switch_is_checked,
      },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if(json == null){
          jQuery.alert({
            title: "Alert!!",
            content:
              "Status is not updated please refresh the page and try again! ",
          });
        }else{
          if (json.status == "limitcrossed") {
            jQuery.alert({
              title: "Your auto update limit is crossed!",
              content:
                "You have already used all of your auto updates for this month. You can change auto update list again on next renewal. Your auto update limit is " +
                json.plan_limit +
                ". This is not applicable for ONETIME license.",
            });
          }

              location.reload(true);
        }

      },
    });
  });

  jQuery("#white_label").click(function () {
    jQuery.alert({
      title: "Sorry!!!",
      content:
        'Your activated plan does not have white label feature. Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
    });
  });

  jQuery("#pluginforceupdate").click(function (event) {
    if (!confirm("Please confirm and auto update will run instantly!"))
      event.preventDefault();
  });


  jQuery("#pluginforceupdateinstant").click(function (event) {
    if (!confirm("Please confirm to run update instantly!"))
      event.preventDefault();
  });

  jQuery("#themeforceupdate").click(function (event) {
    if (!confirm("Please confirm and auto update will run instantly!"))
      event.preventDefault();
  });


  jQuery("#themeforceupdate_instant").click(function (event) {
    if (!confirm("Please confirm and instant update will run!"))
      event.preventDefault();
  });

  jQuery("#manual_force_update_r").click(function () {
    jQuery.alert({
      title: "Sorry!!!",
      content:
        'Your activated plan does not have FORCE UPDATE feature.  Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
    });
  });


  jQuery("#manual_force_update_instant_r").click(function () {
    jQuery.alert({
      title: "Sorry!!!",
      content:
        'Your activated plan does not have instant update feature.  Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
    });
  });

  jQuery("#no_update_available").click(function () {
    jQuery.alert({
      content: "No new update is available at this moment!",
    });
  });

  jQuery("#no_instant_update_available").click(function () {
    jQuery.alert({
      content: "No new update is available at this moment!",
    });
  });

  var ajax_filter_data = {};

  if (plugin_ajax_object.get_curr_screen == "toplevel_page_festinger-vault") {
    jQuery("#ajax-plugin-search-form").ready(function (e) {
      jQuery("#overlay").fadeIn(300);

      load_data(ajax_filter_data);
    });
  }

  jQuery("#filter_type").change(function () {
    var filterValue = jQuery(this).val();
    var row = jQuery(".filter_type_cate_val");

    row.hide();
    row.each(function (i, el) {
      if (jQuery(el).attr("data-type") == filterValue) {
        jQuery(el).show();
      }
    });

    if ("all" == filterValue) {
      row.show();
    }
  });

  // jQuery("#filter_item").change(function () {
  //   var filterValue = jQuery(this).val();
  //   var row = jQuery(".filter_type_cate_val");

  //   row.hide();
  //   row.each(function (i, el) {
  //     if (jQuery(el).attr("data-type") == filterValue) {
  //       jQuery(el).show();
  //     }
  //   });

  //   if ("all" == filterValue) {
  //     row.show();
  //   }
  //   console.log(filterValue);
  // });

  jQuery("#reset_filter").click(function () {
    var filterValue = jQuery(this).val();
    var row = jQuery(".filter_type_cate_val");

    row.show();
    ajax_filter_data = {};

    jQuery("#popular").removeClass("active_button");
    jQuery("#recent").removeClass("active_button");
    jQuery("#featured").removeClass("active_button");
    jQuery("#mylist").removeClass("active_button");
    jQuery("#filter_type").removeClass("active_button");
    jQuery("#filter_item").removeClass("active_button");
    jQuery("#filter_category").removeClass("active_button");
    jQuery("#ajax_search").removeClass("active_button");

    jQuery(function () {
      jQuery("#filter_type").val("all");
      jQuery("#filter_item").val("all");
      jQuery("#filter_category").val("all");
      jQuery("#ajax_search").val("");
    });

    load_data(ajax_filter_data);
  });

  jQuery("#mylist").click(function () {
    var mylist = jQuery("#mylist").val();
    Object.assign(ajax_filter_data, { content_type: mylist });
    jQuery("#popular").removeClass("active_button");
    jQuery("#recent").removeClass("active_button");
    jQuery("#featured").removeClass("active_button");
    jQuery("#mylist").addClass("active_button");
    jQuery("#overlay").fadeIn(300);

    load_data(ajax_filter_data);
  });

  jQuery("#featured").click(function () {
    var featured = jQuery("#featured").val();
    Object.assign(ajax_filter_data, { content_type: featured });
    jQuery("#popular").removeClass("active_button");
    jQuery("#recent").removeClass("active_button");
    jQuery("#mylist").removeClass("active_button");
    jQuery("#featured").addClass("active_button");
    jQuery("#overlay").fadeIn(300);

    load_data(ajax_filter_data);
  });

  jQuery("#popular").click(function () {
    var popular = jQuery("#popular").val();
    Object.assign(ajax_filter_data, { content_type: popular });
    jQuery("#featured").removeClass("active_button");
    jQuery("#recent").removeClass("active_button");
    jQuery("#popular").addClass("active_button");
    jQuery("#mylist").removeClass("active_button");
    jQuery("#overlay").fadeIn(300);
    load_data(ajax_filter_data);
  });

  jQuery("#recent").click(function () {
    var recent = jQuery("#recent").val();
    Object.assign(ajax_filter_data, { content_type: recent });
    jQuery("#featured").removeClass("active_button");
    jQuery("#popular").removeClass("active_button");
    jQuery("#mylist").removeClass("active_button");
    jQuery("#recent").addClass("active_button");
    jQuery("#overlay").fadeIn(300);

    load_data(ajax_filter_data);
  });

  jQuery("#filter_type").change(function () {
    var filter_type = jQuery("#filter_type").val();
    if (filter_type == "all") {
      jQuery("#filter_type").removeClass("active_button");
    } else {
      jQuery("#filter_type").addClass("active_button");
    }
    Object.assign(ajax_filter_data, { filter_type: filter_type });
    jQuery("#overlay").fadeIn(300);

    load_data(ajax_filter_data);
  });


  jQuery("#filter_item").change(function () {
    var filter_item = jQuery("#filter_item").val();
    if (filter_item == "all") {
      jQuery("#filter_item").removeClass("active_button");
    } else {
      jQuery("#filter_item").addClass("active_button");
    }
    Object.assign(ajax_filter_data, { filter_item: filter_item });
    jQuery("#overlay").fadeIn(300);

    load_data(ajax_filter_data);
  });

  jQuery("#filter_category").change(function () {
    var filter_category = jQuery("#filter_category").val();
    if (filter_category == "all") {
      jQuery("#filter_category").removeClass("active_button");
    } else {
      jQuery("#filter_category").addClass("active_button");
    }
    Object.assign(ajax_filter_data, { filter_category: filter_category });
    jQuery("#overlay").fadeIn(300);
    
    load_data(ajax_filter_data);
  });

var timeoutId; // Variable to hold the timeout ID

jQuery("#ajax_search").keyup(function (e) {
  var ajax_search = jQuery("#ajax_search").val();

  if (ajax_search.length >= 1) {
    jQuery("#ajax_search").addClass("active_button");
  } else {
    jQuery("#ajax_search").removeClass("active_button");
  }

  // Clear the previous timeout
  clearTimeout(timeoutId);

  // Set a new timeout of 500 milliseconds
  timeoutId = setTimeout(function () {
    if (e.keyCode == 8 || ajax_search.length >= 3) {
      Object.assign(ajax_filter_data, { search_data: ajax_search });
      jQuery("#overlay").fadeIn(300);

      load_data(ajax_filter_data);
    }
  }, 500);
});


  jQuery("#ajax-license-activation-form").on("submit", function (e) {
    e.preventDefault();
    jQuery("#overlaybef").show();
    jQuery("#overlay").fadeIn(300);
    var licenseKeyInput = jQuery("#licenseKeyInput").val();
    var ajax_url = plugin_ajax_object.ajax_url;

    jQuery
      .ajax({
        data: {
          action: "fv_activation_ajax",
          licenseKeyInput: licenseKeyInput,
        },
        type: "POST",
        url: ajax_url,
        success: function (data) {
          var data_s = data.slice(0, -1);
          var json = JSON.parse(data_s);

          if (json.result == "failed") {
            jQuery("#activation_result").addClass(
              "card text-center text-danger"
            );
            jQuery("#activation_result").removeClass(
              "text-success text-warning"
            );
          } else if (json.result == "invalid") {
            jQuery("#activation_result").addClass(
              "card text-center text-warning"
            );
            jQuery("#activation_result").removeClass(
              "text-success text-danger"
            );
          } else if (json.result == "valid") {
            jQuery("#ajax-license-activation-form").hide();
            jQuery("#activation_result").addClass(
              "card text-center text-success"
            );
            jQuery("#activation_result").removeClass(
              "text-warning text-danger"
            );
          }

          jQuery("#activation_result").html(json.msg);

          setTimeout(function () {
            location.reload();
          }, 3000);
        },
      })
      .done(function () {
        setTimeout(function () {
          jQuery("#overlay").fadeOut(300);
        }, 500);
      });
  });

  jQuery("#ajax-license-refill-form").on("submit", function (e) {
    e.preventDefault();
    jQuery("#overlaybef").show();
    jQuery("#overlay").fadeIn(300);
    var license_key = jQuery("#license_key").val();
    var refill_key = jQuery("#refill_key").val();
    var ajax_url = plugin_ajax_object.ajax_url;

    jQuery
      .ajax({
        data: {
          action: "fv_license_refill_ajax",
          license_key: license_key,
          refill_key: refill_key,
        },
        type: "POST",
        url: ajax_url,
        success: function (data) {
          var data_s = data.slice(0, -1);
          var json = JSON.parse(data_s);

          if (json.result == "success") {
            jQuery("#credit_refill_msg").removeClass("text-danger mb-3");
            jQuery("#credit_refill_msg").addClass("text-success mb-3");
            jQuery(".refill_button").hide();
            jQuery(".refresh_button").show();
            jQuery("#credit_refill_msg").html(json.msg);
          } else {
            jQuery("#credit_refill_msg").removeClass("text-success mb-3");
            jQuery("#credit_refill_msg").addClass("text-danger mb-3");
            jQuery("#credit_refill_msg").html(json.msg);
          }
          jQuery("#refill_key").val("");
        },
      })
      .done(function () {
        setTimeout(function () {
          jQuery("#overlay").fadeOut(300);
        }, 500);
      });
  });

  jQuery("#ajax-license-refill-form2").on("submit", function (e) {
    e.preventDefault();
    jQuery("#overlaybef").show();
    jQuery("#overlay").fadeIn(300);
    var license_key = jQuery("#license_key2").val();
    var refill_key = jQuery("#refill_key2").val();
    var ajax_url = plugin_ajax_object.ajax_url;

    jQuery
      .ajax({
        data: {
          action: "fv_license_refill_ajax",
          license_key: license_key,
          refill_key: refill_key,
        },
        type: "POST",
        url: ajax_url,
        success: function (data) {
          var data_s = data.slice(0, -1);
          var json = JSON.parse(data_s);

          if (json.result == "success") {
            jQuery("#credit_refill_msg").removeClass("text-danger mb-3");
            jQuery("#credit_refill_msg").addClass("text-success mb-3");
            jQuery(".refill_button").hide();
            jQuery(".refresh_button").show();
            jQuery("#credit_refill_msg").html(json.msg);
          } else {
            jQuery("#credit_refill_msg").removeClass("text-success mb-3");
            jQuery("#credit_refill_msg").addClass("text-danger mb-3");
            jQuery("#credit_refill_msg").html(json.msg);
          }

          jQuery("#refill_key2").val("");
        },
      })
      .done(function () {
        setTimeout(function () {
          jQuery("#overlay").fadeOut(300);
        }, 500);
      });
  });

  jQuery(".ajax-license-deactivation-form").on("submit", function (e) {
    e.preventDefault();
    jQuery("#overlaybef").show();
    jQuery("#overlay").fadeIn(300);
    var license_key = jQuery("#license_key").val();
    var license_d = jQuery("#license_d").val();
    var ajax_url = plugin_ajax_object.ajax_url;

    jQuery
      .ajax({
        data: {
          action: "fv_deactivation_ajax",
          license_key: license_key,
          license_d: license_d,
        },
        type: "POST",
        url: ajax_url,
        success: function (data) {
          var data_s = data.slice(0, -1);
          var json = JSON.parse(data_s);

          if (json.result == "failed") {
            jQuery(".deactivation_result").addClass(
              "card text-center text-danger"
            );
            jQuery(".deactivation_result").removeClass(
              "text-success text-warning"
            );
          } else if (json.result == "notfound") {
            jQuery(".deactivation_result").addClass(
              "card text-center text-warning"
            );
            jQuery(".deactivation_result").removeClass(
              "text-success text-danger"
            );
          } else if (json.result == "success") {
            jQuery("#ajax-license-activation-form").hide();
            jQuery(".deactivation_result").addClass(
              "card text-center text-success"
            );
            jQuery(".deactivation_result").removeClass(
              "text-warning text-danger"
            );
          }
          jQuery(".deactivation_result").html(json.msg);
          setTimeout(function () {
            location.reload();
          }, 5000);
        },
      })
      .done(function () {
        setTimeout(function () {
          jQuery("#overlay").fadeOut(300);
        }, 500);
      });
  });

  jQuery(".ajax-license-deactivation-form-2").on("submit", function (e) {
    e.preventDefault();
    jQuery("#overlaybef").show();
    jQuery("#overlay").fadeIn(300);
    var license_key = jQuery("#license_key_2").val();
    var license_d = jQuery("#license_d_2").val();
    var ajax_url = plugin_ajax_object.ajax_url;

    jQuery
      .ajax({
        data: {
          action: "fv_deactivation_ajax_2",
          license_key: license_key,
          license_d: license_d,
        },
        type: "POST",
        url: ajax_url,
        success: function (data) {
          var data_s = data.slice(0, -1);
          var json = JSON.parse(data_s);
          if (json.result == "failed") {
            jQuery(".deactivation_result2").addClass(
              "card text-center text-danger"
            );
            jQuery(".deactivation_result2").removeClass(
              "text-success text-warning"
            );
          } else if (json.result == "notfound") {
            jQuery(".deactivation_result2").addClass(
              "card text-center text-warning"
            );
            jQuery(".deactivation_result2").removeClass(
              "text-success text-danger"
            );
          } else if (json.result == "success") {
            jQuery("#ajax-license-activation-form").hide();
            jQuery(".deactivation_result2").addClass(
              "card text-center text-success"
            );
            jQuery(".deactivation_result2").removeClass(
              "text-warning text-danger"
            );
          }
          jQuery(".deactivation_result2").html(json.msg);
          setTimeout(function () {
            location.reload();
          }, 5000);
        },
      })
      .done(function () {
        setTimeout(function () {
          jQuery("#overlay").fadeOut(300);
        }, 500);
      });
  });

  jQuery(function () {
    jQuery("#toggle-event").bootstrapToggle({
      on: "",
      off: "",
    });
  });
  // ----------------------------------------------------------  REVAMP
});






function grab_product_hash(d) {
  jQuery(".progress").hide();
  jQuery("#overlay").fadeIn(300);
  var product_hash = d.getAttribute("data-id");

  var ajax_url = plugin_ajax_object.ajax_url;


  jQuery
    .ajax({
      data: { action: "fv_plugin_buttons_ajax", product_hash: product_hash },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if (json.result == "failed") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

          jQuery.alert({
            content: json.msg,
          });
        }
        if (json.length == 0) {
          jQuery.alert({
            content: "To enjoy this feature please activate your license.",
          });
        } else {
          collectort(json);
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}







function grab_product_support_link(d) {
      jQuery(".progress").hide();

  jQuery("#overlay").fadeIn(300);
  var data_support_link = d.getAttribute("data-support-link");
  var data_product_hash = d.getAttribute("data-product-hash");
  var data_generated_slug = d.getAttribute("data-generated-slug");
  var data_generated_name = d.getAttribute("data-generated-name");




  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: { action: "fv_plugin_support_link", data_support_link: data_support_link, data_product_hash: data_product_hash, data_generated_slug: data_generated_slug, data_generated_name:data_generated_name },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);


        if (json.result == "success") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);


            button_data = '<div class="row">';
              button_data += '<form class="submitupdaterequest" data-product-hash="'+json.data_product_hash+'" data-generated-slug="'+json.data_generated_slug+'" data-license="'+json.license_key+'">';
              button_data += '<div class="mb-3"><input type="text" class="form-control text-white" name="versionnumberrequest" placeholder="Enter the version number (e.g. 2.3.2)" onkeydown="if(event.keyCode==13){event.preventDefault();}"><input type="hidden" class="form-control text-white" name="versionnumberrequest_link" value="'+data_support_link+'"><input type="hidden" class="form-control text-white" name="data_generated_name" value="'+data_generated_name+'"><input type="hidden" class="form-control text-white" name="licenseKeyGet" value="'+json.license_key+'"> </div><br/><div class="d-grid gap-2 col-12 mx-auto"> <button class="btn btn-secondary" onclick="handleFormSubmit(this)" type="button">Submit</button>  </div>';
              button_data += '</form>';
              button_data += '</div>';


              jQuery(".modal-body").html(button_data);
              jQuery("#empModal").modal("show");




        }


      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}






function grab_product_report_link(d) {
      jQuery(".progress").hide();

  jQuery("#overlay").fadeIn(300);
  var data_support_link = d.getAttribute("data-support-link");
  var data_product_hash = d.getAttribute("data-product-hash");
  var data_generated_slug = d.getAttribute("data-generated-slug");
  var data_generated_name = d.getAttribute("data-generated-name");

  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: { action: "fv_plugin_report_link", data_support_link: data_support_link, data_product_hash: data_product_hash, data_generated_slug: data_generated_slug, data_generated_name:data_generated_name },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);


        if (json.result == "success") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);


            button_data = '<div class="row">';
              button_data += '<form class="submitupdaterequest" data-product-hash="'+json.data_product_hash+'" data-generated-slug="'+json.data_generated_slug+'" data-license="'+json.license_key+'">';
              button_data += '<div class="mb-3"><textarea class="form-control text-white" name="versionnumberrequest" placeholder="Fill in your report and it will be automatically posted on the community forums. You will receive your reported link afterwards."></textarea><input type="hidden" class="form-control text-white" name="versionnumberrequest_link" value="'+data_support_link+'"><input type="hidden" class="form-control text-white" name="data_generated_name" value="'+data_generated_name+'"><input type="hidden" class="form-control text-white" name="licenseKeyGet" value="'+json.license_key+'"> </div><br/><div class="d-grid gap-2 col-12 mx-auto"> <button class="btn btn-secondary" onclick="handleFormSubmitReport(this)" type="button">Submit</button>  </div>';
              button_data += '</form>';
              button_data += '</div>';


              jQuery(".modal-body").html(button_data);
              jQuery("#empModal").modal("show");




        }


      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}





function handleFormSubmit(button) {



  var form = jQuery(button).closest("form"); // get the parent form of the clicked button
  var formData = form.serialize(); // serialize the form data


  // do something with the form data, for example:
  var versionNumber = form.find("input[name='versionnumberrequest']").val();
  var licenseKeyGet = form.find("input[name='licenseKeyGet']").val();
  var data_generated_name = form.find("input[name='data_generated_name']").val();

  if (versionNumber.trim() === "") {
    alert("Version number is required.");
    return;
  }
  jQuery("#empModal").modal("hide");
  jQuery("#overlay").fadeIn(300);
  var getlinkData = form.find("input[name='versionnumberrequest_link']").val();

  var parts = getlinkData.split("/");
  var lastPart = parts.pop();
  var lastNumericValue = parseInt(lastPart);






  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: { action: "fv_discourse_post_new_version", versionNumber: versionNumber, lastNumericValue: lastNumericValue, licenseKeyGet: licenseKeyGet, data_generated_name: data_generated_name },
      type: "POST",
      url: ajax_url,
      success: function (data) {

        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if (json.result == "success") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

              jQuery("#empModal").modal("hide");


          jQuery.alert({
            content: 'You request has been successfully posted. Visit <a target="_blank" href="'+getlinkData+'">this link</a> to see.',
          });
        }


        if (json.result == "failed") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

              jQuery("#empModal").modal("hide");


          jQuery.alert({
            content: json.msg,
          });
        }

      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });






}




function handleFormSubmitReport(button) {


  var form = jQuery(button).closest("form"); // get the parent form of the clicked button
  var formData = form.serialize(); // serialize the form data


  // do something with the form data, for example:
  var versionNumber = form.find("textarea[name='versionnumberrequest']").val();
  var licenseKeyGet = form.find("input[name='licenseKeyGet']").val();
  var data_generated_name = form.find("input[name='data_generated_name']").val();



  if (versionNumber.trim() === "") {
    alert("Version number is required.");
    return;
  }
  jQuery("#empModal").modal("hide");
  jQuery("#overlay").fadeIn(300);

  var getlinkData = form.find("input[name='versionnumberrequest_link']").val();

  var parts = getlinkData.split("/");
  var lastPart = parts.pop();
  var lastNumericValue = parseInt(lastPart);






  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: { action: "fv_discourse_post_new_report", versionNumber: versionNumber, lastNumericValue: lastNumericValue, licenseKeyGet: licenseKeyGet, data_generated_name: data_generated_name },
      type: "POST",
      url: ajax_url,
      success: function (data) {

        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if (json.result == "success") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

              jQuery("#empModal").modal("hide");


          jQuery.alert({
            content: 'You request has been successfully posted. Visit <a target="_blank" href="'+getlinkData+'">this link</a> to see.',
          });
        }


        if (json.result == "failed") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

              jQuery("#empModal").modal("hide");


          jQuery.alert({
            content: json.msg,
          });
        }

      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });






}


function grab_product_dowload_link(d) {
  jQuery("#overlay").fadeIn(300);
  let dd = $(d).find('option:selected');
    // Check if there is only one option in the select


  var plugin_download_hash = dd.data("id");
  var license_key = dd.data("license");
  var data_key = dd.data("key");


  
  if(typeof license_key === 'undefined' || license_key === undefined) {
    var plugin_download_hash = d.getAttribute("data-id");
    var license_key = d.getAttribute("data-license");
    var data_key = d.getAttribute("data-key");
  }

  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: {
        action: "fv_plugin_download_ajax",
        plugin_download_hash: plugin_download_hash,
        license_key: license_key,
        mfile: data_key,
      },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if (json.result == "success") {
          jQuery("#" + license_key + " #plan_limit_id").html(json.plan_limit);
          jQuery("#" + license_key + " #current_limit_id").html(
            json.download_current_limit
          );
          jQuery("#" + license_key + " #limit_available_id").html(
            json.download_available + " / "
          );
          location.href = json.link;
          jQuery("#empModal").modal("hide");
        } else {
          jQuery("#empModal").modal("hide");
          if (json.result == "failed" && json.msg == "Daily limit crossed") {
            if (json.plan_type == "onetime") {
              jQuery.alert({
                title: "Sorry! Limit issue!",
                content:
                  "Your Download Limit is over, For onetime license please refill to enjoy downloading again! Happy downloading.",
              });
            } else {
              jQuery.alert({
                title: "Sorry! Limit issue!",
                content:
                  "Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
              });
            }
          } else {
            if (json.msg) {
              jQuery.alert({
                title: "Alert!",
                content: json.msg,
              });
            } else {
              jQuery.alert({
                title: "Alert!",
                content: "Something went wrong, Please try again later!",
              });
            }
          }
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}



function grab_product_install_bundle_link(d) {
  jQuery(".progress").show();

  jQuery("#overlay").fadeIn(300);
  updateProgressBarAuto(50);

    // Check if there is only one option in the select

  var plugin_download_hash = productHashJson; 

  var license_key = d.getAttribute("data-license");
  var data_key = d.getAttribute("data-key");


  
  if(typeof license_key === 'undefined' || license_key === undefined) {
    var plugin_download_hash = d.getAttribute("data-id");
    var license_key = d.getAttribute("data-license");
    var data_key = d.getAttribute("data-key");
  }

  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: {
        action: "fv_plugin_install_bulk_ajax",
        plugin_download_hash: plugin_download_hash,
        license_key: license_key,
        mfile: data_key,
      },
      type: "POST",
      url: ajax_url,
      success: function (data) {

         // updateProgressBarAuto(100);

        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);



        if (json.result == "success") {
          jQuery("#" + license_key + " #plan_limit_id").html(json.plan_limit);
          jQuery("#" + license_key + " #current_limit_id").html(
            json.download_current_limit
          );
          jQuery("#" + license_key + " #limit_available_id").html(
            json.download_available + " / "
          );



          //location.href = json.link;


         // 
         // jQuery("#empModal").modal("hide");
        } else {

            if (typeof json.result !== 'undefined') {
              if (json.msg) {
                  jQuery.alert({
                    title: "Alert!",
                    content: json.msg,
                  });


                } else {
                  jQuery.alert({
                    title: "Alert!",
                    content: "Something went wrong, Please try again later!",
                  });
                }

              jQuery("#empModal").modal("hide");
              jQuery("#overlay").fadeOut(300);

            }






          //jQuery("#empModal").modal("hide");
          if (json.config.result == "failed" && json.config.msg == "Daily limit crossed") {
            if (json.config.plan_type == "onetime") {
              jQuery.alert({
                title: "Sorry! Limit issue!",
                content:
                  "Your Download Limit is over, For onetime license please refill to enjoy downloading again! Happy downloading.",
              });
            } else {
              jQuery.alert({
                title: "Sorry! Limit issue!",
                content:
                  "Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
              });
            }
          } else {
            if (json.config.msg) {
              jQuery.alert({
                title: "Alert!",
                content: json.config.msg,
              });
            } else {
              // jQuery.alert({
              //   title: "Alert!",
              //   content: "Items has been installed successfully.",
              // });

                updateProgressBar(json.getfilesize);
                

                $.removeCookie('cartData');
                refreshCartDisplay();



            }
          }
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}





function grab_product_dowload_bundle_link(d) {
  jQuery(".progress").show();

  jQuery("#overlay").fadeIn(300);
  updateProgressBarAutoDLBefore(50);
    // Check if there is only one option in the select

  var plugin_download_hash = productHashJson; 

  var license_key = d.getAttribute("data-license");
  var data_key = d.getAttribute("data-key");

  
  if(typeof license_key === 'undefined' || license_key === undefined) {
    var plugin_download_hash = d.getAttribute("data-id");
    var license_key = d.getAttribute("data-license");
    var data_key = d.getAttribute("data-key");
  }

  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: {
        action: "fv_plugin_download_ajax_bundle",
        plugin_download_hash: plugin_download_hash,
        license_key: license_key,
        mfile: data_key,
      },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        //updateProgressBar(50);

        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);


        if (typeof json.result !== 'undefined') {
          if (json.msg) {
              jQuery.alert({
                title: "Alert!",
                content: json.msg,
              });
      

            } else {
              jQuery.alert({
                title: "Alert!",
                content: "Something went wrong, Please try again later!",
              });
            }

          jQuery("#empModal").modal("hide");
          jQuery("#overlay").fadeOut(300);

        }


        if (typeof json.config.result != 'undefined' && json.config.result == "success") {
          jQuery("#" + license_key + " #plan_limit_id").html(json.config.plan_limit);
          jQuery("#" + license_key + " #current_limit_id").html(
            json.config.download_current_limit
          );
          jQuery("#" + license_key + " #limit_available_id").html(
            json.config.download_available + " / "
          );


 
          location.href = json.links;

          updateProgressBarAutoDL(100);

          $.removeCookie('cartData');
          refreshCartDisplay();

         // 
         // jQuery("#empModal").modal("hide");
        } else {
          jQuery("#empModal").modal("hide");
          if (json.config.result == "failed" && json.config.msg == "Daily limit crossed") {
            if (json.config.plan_type == "onetime") {
              jQuery.alert({
                title: "Sorry! Limit issue!",
                content:
                  "Your Download Limit is over, For onetime license please refill to enjoy downloading again! Happy downloading.",
              });
            } else {
              jQuery.alert({
                title: "Sorry! Limit issue!",
                content:
                  "Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
              });
            }
          } else {
            if (json.config.msg) {
              jQuery.alert({
                title: "Alert!",
                content: json.config.msg,
              });
      

            } else {
              jQuery.alert({
                title: "Alert!",
                content: "Something went wrong, Please try again later!",
              });
            }
          }
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}



//progressbar start



 function updateProgressBarAuto(progress) {
  var progressElement = $('.progress');
  var currentProgress = parseInt(progressElement.attr('aria-valuenow'));
  var targetProgress = parseInt(progress);

  var increment = (targetProgress - currentProgress) / 100;

  function incrementProgress() {
    currentProgress += increment;
    progressElement.attr('aria-valuenow', currentProgress);
    progressElement.find('.progress-bar').css('width', currentProgress + '%');

    if (currentProgress >= targetProgress) {
      return;
    }

    setTimeout(incrementProgress, 100);
  }

  incrementProgress();
}



 function updateProgressBarAutoDLBefore(progress) {
  var progressElement = $('.progress');
  var currentProgress = parseInt(progressElement.attr('aria-valuenow'));
  var targetProgress = parseInt(progress);

  var increment = (targetProgress - currentProgress) / 100;



  function incrementProgress() {
    currentProgress += increment;
    progressElement.attr('aria-valuenow', currentProgress);
    progressElement.find('.progress-bar').css('width', currentProgress + '%');


    if (currentProgress >= targetProgress) {
   
      return;
    }

    setTimeout(incrementProgress, 60);
  }

  incrementProgress();


}



 function updateProgressBarAutoDL(progress) {
  var progressElement = $('.progress');
  var currentProgress = parseInt(progressElement.attr('aria-valuenow'));
  var targetProgress = parseInt(progress);

  var increment = (targetProgress - currentProgress) / 100;



  function incrementProgress() {
    currentProgress += increment;
    progressElement.attr('aria-valuenow', currentProgress);
    progressElement.find('.progress-bar').css('width', currentProgress + '%');

  if(currentProgress >= 100 ){
      setTimeout(function() {
        jQuery("#empModal").modal("hide");
      }, 1000); // Delay of 1000 milliseconds (1 second)

  }
    if (currentProgress >= targetProgress) {
   
      return;
    }

    setTimeout(incrementProgress, 5);
  }

  incrementProgress();


}


function updateProgressBar(fileSize) {


  var progressBar = $('.progress');
  var currentProgress = parseInt(progressBar.attr('aria-valuenow'));
  var targetProgress = 100-currentProgress; // The target progress is 100% (full progress bar)
  // Calculate the estimated increment per chunk
  var chunkSize = 1024 * 80; // 10 KB (adjust as needed)
  var totalChunks = Math.ceil(fileSize / chunkSize);
  var increment = targetProgress / totalChunks;
  
  // Simulate download progress
  var currentChunk = 0;
  var interval = setInterval(function() {
    currentChunk++;
    currentProgress += increment;
   // progressBar.css('width', currentProgress + '%').attr('aria-valuenow', currentProgress.toFixed(2));
    progressBar.attr('aria-valuenow', currentProgress);
    progressBar.find('.progress-bar').css('width', currentProgress + '%');

    // Check if the target progress is reached
    if (currentChunk >= totalChunks) {
      clearInterval(interval);
      
      setTimeout(function() {
        jQuery("#empModal").modal("hide");
      }, 1000); // Delay of 1000 milliseconds (1 second)

    }


  }, 100); // Adjust the interval in milliseconds (e.g., 100 for smoother animation)
}




//progressbar end




function collectortMultiple(json, type) {

  if(type == 'download'){
    var typeBtnText = 'Download Bundle';
    var typeBtnIcon = 'fa fa-download';
    var typeBtnMethod = 'grab_product_dowload_bundle_link';
  }else if(type == 'install'){
    var typeBtnText = 'Install Bundle';
    var typeBtnIcon = 'fas fa-cloud-download-alt';
    var typeBtnMethod = 'grab_product_install_bundle_link';
  }

  var button_data = '<div class="row">';

  jQuery.each(json, function (index, item) {
    var ind_item = JSON.parse(item);

    productHashJson = (ind_item.product_hash);

    button_data +=
      '<div class="col"><div class="card bg-light" style="min-width:100%;"> ';
    button_data += '<div class="card-header">';
    button_data += ind_item.plan_name;
    button_data += "</div>";
    button_data += '<ul class="list-group list-group-flush">';
    button_data +=
      '<li class="list-group-item">Plan Type<b>: ' +
      ind_item.plan_type.toUpperCase() +
      "</b></li>";
    button_data +=
      '<li class="list-group-item">Plan Limit: ' +
      ind_item.plan_limit +
      "</li>";
    button_data +=
      '<li class="list-group-item">Available Limit: ' +
      ind_item.download_available +
      "</li>";
    button_data += "</ul>";
    button_data += "</div>";


    button_data += "<div class='row'> <div class='text-white mt-2 mb-2'> Plugins Bundle </div>";
      button_data += "<ui>";
        button_data += "<ui class='text-white'>";
          jQuery.each(ind_item.product_hash, function (index2, item2) {
            button_data += "<li>";
              button_data += item2.product_name;
            button_data += "</li>";
          });

      button_data += "</ui>";


    button_data += "</div>";


    button_data +=
      '<button id="option1" data-license="' +
      ind_item.license_key +
      '" data-id="' +
      productHashJson +
      '" onclick="'+typeBtnMethod+'(this); this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="'+typeBtnIcon+'"></i>'+ typeBtnText +
      
      "  </button> ";






    button_data += "</div>";



  });
  button_data += "</div>";




    



  jQuery(".modal-body").html(button_data);
  jQuery("#empModal").modal("show");
  setTimeout(function () {
    jQuery("#overlay").fadeOut(300);
  }, 500);
}





function collectort(json) {

  var button_data = '<div class="row">';

  jQuery.each(json, function (index, item) {
    var ind_item = JSON.parse(item);

    let count_versions = (ind_item.other_available_versions.length);
    let whichevent = 'onChange';
    if(count_versions == 1){
      whichevent = 'onClick';

    }
    button_data +=
      '<div class="col"><div class="card bg-light" style="min-width:100%;"> ';
    button_data += '<div class="card-header">';
    button_data += ind_item.plan_name;
    button_data += "</div>";
    button_data += '<ul class="list-group list-group-flush">';
    button_data +=
      '<li class="list-group-item">Plan Type<b>: ' +
      ind_item.plan_type.toUpperCase() +
      "</b></li>";
    button_data +=
      '<li class="list-group-item">Plan Limit: ' +
      ind_item.plan_limit +
      "</li>";
    button_data +=
      '<li class="list-group-item">Available Limit: ' +
      ind_item.download_available +
      "</li>";
    button_data += "</ul>";
    button_data += "</div>";
    button_data +=
      '<button id="option1" data-license="' +
      ind_item.license_key +
      '" data-id="' +
      ind_item.product_hash +
      '" onclick="grab_product_dowload_link(this); this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-download"></i>Download ' +
      
      " LATEST VERSION </button> ";

      button_data += '<div class="row" style="margin-top:40px;">';

        button_data += '<div class="col">';
          button_data += '<table class="table table-bordered" style="color:#fff;">';
                  button_data += "<tr>";
                    button_data += "<td>";

                      button_data += '<div class="input-group text-white">';
                      button_data += '<label class="input-group-text" for="inputGroupSelect01">Please choose your preferred version. Once selected, it will be installed and activated automatically</label>';
                      button_data += '<select '+whichevent+'="grab_product_dowload_link(this); this.disabled=true;" class="form-select text-white '+ind_item.license_key+ind_item.product_hash+'" name="downloadOtherVerions">';
                      
                      /*
                      jQuery.each(ind_item.other_available_versions.reverse(), function (index2, item2) {

                        button_data += '<option value="'+item2.generated_version+'" data-key="'+item2.filename+'" data-license="' +
                                                ind_item.license_key +
                                                '" data-id="' +
                                                ind_item.product_hash +
                                                '" >Version '+item2.generated_version+'</option>';

                      });

                      */
                      if (Array.isArray(ind_item.other_available_versions)) {
                        jQuery.each(ind_item.other_available_versions.reverse(), function (index2, item2) {
                          button_data += '<option value="'+item2.generated_version+'" data-key="'+item2.filename+'" data-license="' +
                            ind_item.license_key +
                            '" data-id="' +
                            ind_item.product_hash +
                            '" >Version '+item2.generated_version+'</option>';
                        });
                      }




                      button_data += '</select>';

                      /*button_data +=  '<button id="option1" data-license="' +
                                        ind_item.license_key +
                                        '" data-id="' +
                                        ind_item.product_hash +
                                        '" onclick="grab_product_dowload_link(this); this.disabled=true;" class="btn btn-outline-secondary card-btn"><i class="fa fa-download"></i>Download from ' +
                                        ind_item.plan_type.toUpperCase() +
                                        " plan </button> </div>";*/
                    button_data += "</td>";
                  button_data += "</tr>";


          button_data += "</table>";
        button_data += "</div>";
      button_data += "</div>";



    button_data += "</div>";



  });
  button_data += "</div>";




    



  jQuery(".modal-body").html(button_data);
  jQuery("#empModal").modal("show");
  setTimeout(function () {
    jQuery("#overlay").fadeOut(300);
  }, 500);
}

function grab_product_install_hash(d) {
    jQuery(".progress").hide();

  jQuery("#overlay").fadeIn(300);
  var product_hash = d.getAttribute("data-id");


  //var license_key = dd.data("license");
  //var data_key = dd.data("key");


  var ajax_url = plugin_ajax_object.ajax_url;
  jQuery
    .ajax({
      data: {
        action: "fv_plugin_install_button_modal_generate",
        product_hash: product_hash,
      },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);
        if (json.result == "failed") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);
          jQuery.alert({
            content: json.msg,
          });
        }

        if (json.length == 0) {
          jQuery.alert({
            content: "To enjoy this feature please activate your license.",
          });
        } else {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

          install_btn_modal_pop_button(json);
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}

function install_btn_modal_pop_button(json) {
  var generate_install_link = "";
  var button_data = '<div class="row">';
  jQuery.each(json, function (index, item) {
    var ind_item = JSON.parse(item);
    let count_versions = (ind_item.other_available_versions.length);
    let whichevent = 'onChange';
    if(count_versions == 1){
      whichevent = 'onClick';

    }

    var install_and_activated_text;

    if (ind_item.product_type == "wordpress-themes") {
      install_and_activated_text = "Install";
    } else if (ind_item.product_type == "wordpress-plugins") {
    install_and_activated_text = "Install & Activate";
  }
    generate_install_link =
      '<button id="option1" data-license="' +
      ind_item.license_key +
      '" data-type="' +
      ind_item.product_type +
      '" data-id="' +
      ind_item.product_hash +
      '" href="#" onclick="grab_product_install_link(this);this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>' +
      install_and_activated_text +
      " LATEST VERSION  " +
      "  </button>";

    if (
      jQuery.inArray(
        ind_item.product_slug,
        JSON.parse(plugin_ajax_object.get_all_active_themes_js)
      ) !== -1
    ) {
      //generate_install_link =
        //'<button class="btn btn-sm btn-block disabled card-btn"><i class="fa fa-arrow-down"></i>Already Installed </button>';
          generate_install_link =
              '<button id="option1" data-license="' +
              ind_item.license_key +
              '" data-type="' +
              ind_item.product_type +
              '" data-id="' +
              ind_item.product_hash +
              '" href="#" onclick="grab_product_install_link(this);this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>Install latest version</button>';
          
    }

    if (
      jQuery.inArray(
        ind_item.product_slug,
        JSON.parse(plugin_ajax_object.get_all_active_plugins_js)
      ) !== -1
    ) {
      generate_install_link =
        '<button id="option1" data-license="' +
        ind_item.license_key +
        '" data-type="' +
        ind_item.product_type +
        '" data-id="' +
        ind_item.product_hash +
        '" href="#" onclick="grab_product_install_link(this);this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>Install latest version</button>';
    }

    if (
      jQuery.inArray(
        ind_item.product_slug,
        JSON.parse(plugin_ajax_object.get_all_inactive_themes_js)
      ) !== -1
    ) {
      generate_install_link =
        '<button id="option1" data-license="' +
        ind_item.license_key +
        '" data-type="' +
        ind_item.product_type +
        '" data-id="' +
        ind_item.product_hash +
        '" href="#" onclick="grab_product_install_link(this);this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>Already Installed Please Activate</button>';
    }

    if (
      jQuery.inArray(
        ind_item.product_slug,
        JSON.parse(plugin_ajax_object.get_all_inactive_plugins_js)
      ) !== -1
    ) {
      generate_install_link =
        '<button id="option1" data-license="' +
        ind_item.license_key +
        '" data-type="' +
        ind_item.product_type +
        '" data-id="' +
        ind_item.product_hash +
        '" href="#" onclick="grab_product_install_link(this);this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>Already Installed Please Activate</button>';
    }

    button_data +=
      '<div class="col"><div class="card bg-light" style="min-width:100%;">';
    button_data += '<div class="card-header">';
    button_data += ind_item.plan_name;
    button_data += "</div>";
    button_data += '<ul class="list-group list-group-flush">';
    button_data +=
      '<li class="list-group-item">Plan Type<b>: ' +
      ind_item.plan_type.toUpperCase() +
      "</b></li>";
    button_data +=
      '<li class="list-group-item">Plan Limit: ' +
      ind_item.plan_limit +
      "</li>";
    button_data +=
      '<li class="list-group-item">Available Limit: ' +
      ind_item.download_available +
      "</li>";
    button_data += "</ul>";
    button_data += "</div>";
    button_data += generate_install_link;

      button_data += '<div class="row" style="margin-top:40px;">';
        button_data += '<div class="col">';
          button_data += '<table class="table table-bordered" style="color:#fff;">';
              
          button_data += "<tr>";
                    button_data += "<td>";

                      button_data += '<div class="input-group text-white">';
                      button_data += '<label class="input-group-text" for="inputGroupSelect01">Please choose your preferred version. Once selected, it will be installed and activated automatically</label>';
                      button_data += '<select '+whichevent+'="grab_product_install_link(this); this.disabled=true;" class="form-select text-white '+ind_item.license_key+ind_item.product_hash+'" name="downloadOtherVerions">';
                      
                      /*

                      jQuery.each(ind_item.other_available_versions.reverse(), function (index3, item3) {

                        button_data += '<option value="'+item3.generated_version+'" data-license="' +
                                              ind_item.license_key +
                                              '" data-type="' +
                                              ind_item.product_type +
                                              '" data-id="' +
                                              ind_item.product_hash +
                                              '" data-key="' +
                                              item3.filename +
                                              '" >Version '+item3.generated_version+'</option>';

                      });

                      */


                      if (Array.isArray(ind_item.other_available_versions)) {
                        jQuery.each(ind_item.other_available_versions.reverse(), function (index3, item3) {
                          button_data += '<option value="'+item3.generated_version+'" data-license="' +
                                            ind_item.license_key +
                                            '" data-type="' +
                                            ind_item.product_type +
                                            '" data-id="' +
                                            ind_item.product_hash +
                                            '" data-key="' +
                                            item3.filename +
                                            '" >Version '+item3.generated_version+'</option>';
                        });
                      }



                      button_data += '</select>';

                    button_data += "</td>";
                  button_data += "</tr>";

          button_data += "</table>";
        button_data += "</div>";
      button_data += "</div>";

    button_data += "</div>";
  });
  button_data += "</div>";
  jQuery(".modal-body").html(button_data);
  jQuery("#empModal").modal("show");
}

function grab_product_install_link(d) {
  jQuery("#overlay").fadeIn(300);

  let ddd = $(d).find('option:selected');

  var plugin_download_hash = ddd.data("id");
  var license_key = ddd.data("license");
  var mfile = ddd.data("key");

  // show confirm popup

  var result = confirm("Are you sure you want to continue?");
  if (!result) {
    jQuery("#overlay").fadeOut(300);
    jQuery("#empModal").modal("hide");
  }else{

    if(typeof license_key === 'undefined' || license_key === undefined) {
      var plugin_download_hash = d.getAttribute("data-id");
      var license_key = d.getAttribute("data-license");
      var mfile = d.getAttribute("data-key");
      conditionmatch = true;

    }



  //var plugin_download_hash = d.getAttribute("data-id");
  //var license_key = d.getAttribute("data-license");
 // var mfile = d.getAttribute("data-key");
  var ajax_url = plugin_ajax_object.ajax_url;

  jQuery
    .ajax({
      data: {
        action: "fv_plugin_install_ajax",
        plugin_download_hash: plugin_download_hash,
        license_key: license_key,
        mfile: mfile,
      },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if (json.result == "success") {
          jQuery("#" + license_key + " #plan_limit_id").html(json.plan_limit);
          jQuery("#" + license_key + " #current_limit_id").html(
            json.download_current_limit
          );
          jQuery("#" + license_key + " #limit_available_id").html(
            json.download_available + " / "
          );

          if (json.link == "theme") {
            jQuery.alert({
              content:
                'Theme successfully installed. Click here to <a target="_blank" href="' +
                json.theme_preview +
                '">Preview theme</a>!',
            });
          } else {
            location.href = json.activation;
          }
          jQuery("#empModal").modal("hide");
        } else {
          jQuery("#empModal").modal("hide");
          if (json.result == "failed" && json.msg == "Daily limit crossed") {
            jQuery.alert({
              title: "Sorry! Limit issue!",
              content:
                "Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
            });
          } else {
            if (json.msg) {
              jQuery.alert({
                title: "Alert!",
                content: json.msg,
              });
            } else {
              jQuery.alert({
                title: "Alert!",
                content: "Hello!Something went wrong, Please try again later!",
              });
            }
          }
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
  }
}

jQuery.date = function (orginaldate) {
  var date = new Date(orginaldate);
  var dates = new Date(orginaldate);
  var day = date.getDate();
  var month = date.getMonth() + 1;
  var year = date.getFullYear();
  if (day < 10) {
    day = "0" + day;
  }
  if (month < 10) {
    month = "0" + month;
  }
  var months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];
  final_month = months[dates.getMonth()];
  var date = final_month + " " + day + ", " + year;

  return date;
};

//var page = 1;

function load_data(ajax_search = "", page = 1) {



  var ajax_url = plugin_ajax_object.ajax_url;
  var show_title_img_fv_link = plugin_ajax_object.show_title_img_fv_link;
  var cdl_allow = plugin_ajax_object.cdl_allow;
  jQuery
    .ajax({
      data: { action: "fv_search_ajax_data", ajax_search: ajax_search, page:page },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        //jQuery("#overlay").fadeIn(300);


        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);
        console.log(json);
        var here_install_button = "";
        var featured_button = "";

        paginationDataController(ajax_search, json.links, page);

        jQuery("#list").pagination({
          dataSource: json.data,
          pageSize: 40,

          showPrevious: false,
          showNext: false,
          showNavigator: false,

          callback: function (data, pagination) {
          jQuery('.paginationjs-page').addClass('disabled disablendhide').attr('disabled', true);


            var wrapper = jQuery("#list .wrapper").empty();
            var row_start = '<div class="row">';
            var row_end = "</div>";
            jQuery("#list .wrapper").append(row_start);
            var plugin_list_data = '<div class="row mb-3 ">';
            var j = 1;
            var col_md_3_visible = "";
            if (data.length < 4) {
              col_md_3_visible = "col-md-3";
            } else {
              col_md_3_visible = "col";
            }
            if (data.length == 0) {
              jQuery("#list .wrapper").html(
                '<div class=mt-4 mb-4"" style="color:#fff; font-size:20px; text-align:center;">Sorry, No plugins or themes are found!</div>'
              );
              jQuery(".paginationjs").hide();
            } else {
              jQuery("html, body").animate(
                {
                  scrollTop: 0,
                },
                100
              );

              jQuery(".paginationjs").show();
            }

            var get_list_wrapper_display_size = jQuery(".wrapper").width();
            var final_show_num_of_items = 4;

            if (
              get_list_wrapper_display_size > 900 &&
              get_list_wrapper_display_size < 1500
            ) {
              final_show_num_of_items = 4;
            } else if (
              get_list_wrapper_display_size > 2000 &&
              get_list_wrapper_display_size < 3999
            ) {
              final_show_num_of_items = 6;
            } else if (get_list_wrapper_display_size > 4000) {
              final_show_num_of_items = 8;
            } else {
              final_show_num_of_items = 2;
            }

            jQuery.each(data, function (i, f) {

              var here_download_button = "";
              var here_details_button = "";
              var disable_the_button = "";
              var is_req_text = "";
              var allowenceImage = "";

              if (cdl_allow == 0) {
                disable_the_button = "disabled";
              }

              if(f.category_slug == 'wishlist' || f.type_slug == 'wishlist'){
                disable_the_button = "disabled";
              }

              var fv_wl_link_allow_first_part = "";
              var fv_wl_link_allow_last_part = "";

              if (show_title_img_fv_link == 1) {
                fv_wl_link_allow_first_part =
                  "<a href='" +
                  f.href +
                  "' style='text-decoration:none;' target='_blank'>";
                fv_wl_link_allow_last_part = "</a>";
              }

              if (f.featured == 1 || f.featured == "1") {
                // featured_button =
                //   '<div style="position: absolute; margin-top: -24px; background: #5a00f0; padding: 4px 12px; border-radius: 12px; margin-left: 36%; font-size: 12px; letter-spacing: .5px; font-weight: 600;"> Featured</div>  ';
                featured_button =
                  '<div style="position: absolute;top:0; margin-top: -28px; background: #4d378e; padding: 4px 12px; border-top-left-radius: 12px;border-top-right-radius: 12px; font-size: 12px; letter-spacing: .5px; color: #fff; font-weight:400"> Featured</div>  ';
              } else {
                featured_button = "";
              }
              here_install_button =
                '<button data-id="' +
                f.unique_rand_md5 +
                '" href="#" onclick="grab_product_install_hash(this);"  class="btn ' +
                disable_the_button +
                ' btn-sm btn-block card-btn" > <i class="fas fa-cloud-download-alt"></i> Install  </button>';
              if (f.image == null) {
                f.image =
                  "https://festingervault.com/wp-content/uploads/2020/12/unnamed-1.jpg";
              }else{
          f.image = f.image;
        }
              if (f.type_slug == "wordpress-requests") {
                here_install_button =
                  '<a class="btn btn-sm btn-block card-btn" style="font-size:12.6px;padding:13px;" target="_blank" href="' +
                  f.href +
                  '"><i class="fas fa-external-link-alt"></i>Request Download</a>';
              }

              if (f.type_slug == "wordpress-requests") {
                is_req_text =
                  '<div class="card card-body" style="padding:5px!important; background:#333333; color:#fff;"> This item is requested, but not available for download yet. Vote for it and it will be added! </div>';
              } else {
                is_req_text = "";
              }

              if (
                jQuery.inArray(
                  f.new_generated_slug,
                  JSON.parse(plugin_ajax_object.get_all_active_themes_js)
                ) !== -1
              ) {
                here_install_button =
                '<button data-id="' +
                f.unique_rand_md5 +
                '" href="#" onclick="grab_product_install_hash(this);"  class="btn ' +
                disable_the_button +
                ' btn-sm btn-block card-btn" > <i class="fas fa-cloud-download-alt"></i> Change Version  </button>';
              }

              if (
                jQuery.inArray(
                  f.new_generated_slug,
                  JSON.parse(plugin_ajax_object.get_all_active_plugins_js)
                ) !== -1
              ) {
                here_install_button =
                '<button data-id="' +
                f.unique_rand_md5 +
                '" href="#" onclick="grab_product_install_hash(this);"  class="btn ' +
                disable_the_button +
                ' btn-sm btn-block card-btn" > <i class="fas fa-cloud-download-alt"></i> Change Version  </button>';
              }

              if (
                jQuery.inArray(
                  f.new_generated_slug,
                  JSON.parse(plugin_ajax_object.get_all_inactive_themes_js)
                ) !== -1
              ) {
                if (f.type_slug == "wordpress-themes") {
                  here_install_button =
                '<button data-id="' +
                f.unique_rand_md5 +
                '" href="#" onclick="grab_product_install_hash(this);"  class="btn ' +
                disable_the_button +
                ' btn-sm btn-block card-btn" > <i class="fas fa-cloud-download-alt"></i> Change Version  </button>';
                } else {
                  here_install_button =
                    '<button data-id="' +
                    f.unique_rand_md5 +
                    '"  onclick="grab_product_install_hash(this);" class="btn ' +
                    disable_the_button +
                    ' btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>Activate</button>';
                }
              }

              if (
                jQuery.inArray(
                  f.new_generated_slug,
                  JSON.parse(plugin_ajax_object.get_all_inactive_plugins_js)
                ) !== -1
              ) {
                if (f.type_slug == "wordpress-themes") {
                  here_install_button =
                    '<a data-id="' +
                    f.unique_rand_md5 +
                    '"  href="themes.php?theme=' +
                    f.preview +
                    '" target="_blank" class="btn ' +
                    disable_the_button +
                    ' btn-sm btn-block card-btn"><i class="fas fa-eye"></i>Sales Page</a>';
                } else {
                  here_install_button =
                    '<button data-id="' +
                    f.unique_rand_md5 +
                    '" onclick="grab_product_install_hash(this);" class="btn ' +
                    disable_the_button +
                    ' btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>Activate</button>';
                }
              }
              if (f.type_slug != "wordpress-requests") {
                here_download_button =
                  '<div class="col-6 mb-1"> <button id="option1" data-id="' +
                  f.unique_rand_md5 +
                  '" data-itemname="' +
                  f.title +
                  '" href="#" onclick="grab_product_hash(this);" class="btn ' +
                  disable_the_button +
                  ' btn-sm btn-block card-btn"> <i class="fas fa-download"></i> Download </button></div>';
              }

      
        if(f.type_slug == 'elementor-template-kits'){
                  here_install_button =
                    '<button class="btn ' +
                    disable_the_button +
                    ' btn-sm btn-block card-btn" disabled><i class="fa fa-arrow-down"></i>Install</button>';          
        }
        
              
              if (f.type_slug != "wordpress-requests") {
                here_details_button =
                  '<div class="col-6 mt-1 mb-1"><a target="_blank" rel="noreferrer" style="font-size:12.6px;" href="' +
                  f.preview +
                  '" class="btn btn-sm btn-block card-btn"> <i class="fas fa-eye"></i> Sales Page </a> </div>';
              }

              var here_support_button =
                '<div class="col-6 mt-1">  <button id="requestupdate" data-support-link="' +
                  f.support_link +
                  '"data-product-hash="' +
                  f.unique_rand_md5 +
                  '"data-generated-slug="' +
                  f.new_generated_slug +
                  '"data-generated-name="' +
                  f.title +
                  '" href="#" onclick="grab_product_support_link(this);" class="btn ' + ' btn-sm btn-block card-btn '+disable_the_button+'"><i class="fas fa-sync"></i>Request Update</button>  </div>';
     



              var here_report_button =
                '<div class="col-6 mt-1">  <button id="reportitem" data-support-link="' +
                  f.support_link +
                  '"data-product-hash="' +
                  f.unique_rand_md5 +
                  '"data-generated-slug="' +
                  f.new_generated_slug +
                  '"data-generated-name="' +
                  f.title +
                  '" href="#" onclick="grab_product_report_link(this);" class="btn ' + ' btn-sm btn-block card-btn "><i class="fas fa-flag"></i> Report Item </button>  </div>';
                   


              var here_support_button_just_link =
                '<div class="col-6 mt-1 mb-1"> <a target="_blank" style="font-size:12.6px;" href="' +
                f.support_link +
                '" class="btn btn-sm btn-block card-btn"> <i class="fas fa-life-ring"></i> Support </a> </div>';
     

              if(show_title_img_fv_link == 0){
                var here_support_button_just_link =
                '<div class="col-6 mt-1 mb-1"> <a style="font-size:12.6px;" href="#" class="btn btn-sm btn-block card-btn disabled"> <i class="fas fa-life-ring"></i> Support </a> </div>';
              
                var here_report_button =
                '<div class="col-6 mt-1 mb-1"> <a style="font-size:12.6px;" href="#" class="btn btn-sm btn-block card-btn disabled"> <i class="fas fa-flag"></i> Report Item  </a> </div>';
              
                var here_support_button =
                '<div class="col-6 mt-1 mb-1"> <a style="font-size:12.6px;" href="#" class="btn btn-sm btn-block card-btn disabled"> <i class="fas fa-sync"></i>Request Update</a> </div>';
              

              }
                
                var here_multiple_download_button =
                '<div class="col-6 mt-1 mb-1"> <button type="button" style="font-size:12.6px;" data-id="' +
                  f.unique_rand_md5 +
                  '" data-itemname="' + f.title + '" onclick="add_to_cart_fv(this)" class="btn btn-sm btn-block card-btn mt-2 add_to_bulk_btn '+disable_the_button+'"> <i class="fas fa-angle-down"></i> Add to bulk </button> </div>';
              

         

                here_virus_scan_button =
                  '<div class="col-6 mt-1 mb-1"><a target="_blank" rel="noreferrer" style="font-size:12.6px;" href="' +
                  f.virusscanurl +
                  '" class="btn btn-sm btn-block card-btn mt-2"> <i class="fas fa-virus-slash"></i> Virustotal Scan</a> </div>';
              

                if(f.virusscanurl == null || f.virusscanurl == ''){
                  here_virus_scan_button =
                    '<div class="col-6 mt-1 mb-1"> <button type="button" style="font-size:12.6px;" class="btn btn-sm btn-block card-btn mt-2 add_to_bulk_btn" disabled> <i class="fas fa-virus-slash"></i> Virustotal Scan</button> </div>';
                }

                if(f.type_slug == 'elementor-template-kits'){
                  here_multiple_download_button =
                    '<div class="col-6 mt-1 mb-1"> <button type="button" style="font-size:12.6px;" class="btn btn-sm btn-block card-btn mt-2 add_to_bulk_btn" disabled> <i class="fas fa-angle-down"></i> Add to bulk </button> </div>';
                }




                here_download_content_button =
                  '<div class="col-12 mt-1"> <button id="optiondc" data-id="' +
                  f.unique_rand_md5 +
                  '" data-itemname="' +
                  f.title +
                  '" href="#" grab_dc_product_hash onclick="grab_dc_product_dcontents(this);" class="btn ' +
                  disable_the_button +
                  ' btn-sm btn-block card-btn"> <i class="fas fa-download"></i> Additional Content </button></div>';



              var summary = f.summary;

              if (summary.length > 99) {
                summary = summary.substring(0, 99) + "...";
              } else {
                summary = "";
              }

              if (f.membershipallowance === null || typeof f.membershipallowance === 'undefined' || f.membershipallowance === '') {

              } else {
                var json22 = JSON.parse(f.membershipallowance);
                var values = Object.values(json22);
                let dl_linkxyzzu = '#';
                if(show_title_img_fv_link != 0){
                  dl_linkxyzzu = 'https://community.festingervault.com/t/gold-silver-and-bronze-downloads/35448';
                }
                $.each(values, function(index, value) {
                    if(value == 'bronze'){
                      allowenceImage += "<a href='"+dl_linkxyzzu+"' target='_blank'><img style='height:20px; float:right;' src='https://festingervault.com/wp-content/uploads/2021/08/Orange-Quest-Medal.png' title='Bronze Download'> </a>";
                    }
                    if(value == 'gold'){
                      allowenceImage += "<a href='"+dl_linkxyzzu+"' target='_blank'><img style='height:20px; float:right;' src='https://festingervault.com/wp-content/uploads/2021/08/Gold-Quest-Medal.png' title='Gold Download'> </a>";
                    }
                    if(value == 'silver'){
                      allowenceImage += "<a href='"+dl_linkxyzzu+"' target='_blank'><img style='height:20px; float:right;' src='https://festingervault.com/wp-content/uploads/2021/08/Silver-Quest-Medal.png' title='Silver Download'> </a>";
                    }

                })

              }


              plugin_list_data +=
                '<div class="col margin-bottom-xs my-4 rounded-lg"><div style="max-width:350px;" class="card h-100 border-8 hover-elevate light-blue light-border"> ' +
                fv_wl_link_allow_first_part +
                '<div class="p-2">' +
                featured_button +
                '<img src="' +
                f.image +
                '" class="card-img-top card-rounded-img" alt="' +
                f.title +
                '" style="height:155px;"> </div>' +
                fv_wl_link_allow_last_part +
                ' <div class="card-body light-blue" style=" color:#f4f5f6; padding:0px;"> <div  style="border-bottom:solid 1px #4d378e;">  </div> <div class="light-border-bottom" style="padding:16px 10px;"> ' +
                fv_wl_link_allow_first_part +
                ' <h5 class="card-title  cut-the-text" style="font-size: 1.125rem; color:#f4f5f6;font-weight:700;margin-bottom:3px;">' +
                (f.title.length > 60
                  ? f.title.substring(0, 60) + "..."
                  : f.title) +
                "</h5> " +
                fv_wl_link_allow_last_part +
                ' <p class="card-text" style="color: #cfcfcf;font-size:.75rem;font-weight:700;letter-spacing:.025rem;text-transform:uppercase;"> ' +
                f.category_slug
                  .replace("-", " ")
                  .toUpperCase()
                  .replace("-", " ") 
                +allowenceImage+"</p> </div>  " +
                // '<div style="padding: 0px 10px;font-size:.875rem!important;color:#cfcfcf!important; "> ' +
                // summary +
                // " " +
                // is_req_text +
                // '</div>'
                '<div class="card-title d-flex justify-content-between " style="font-size:12px; padding-top:10px;padding-left:10px;padding-right:10px;"> ' +
                ' <div class=""> ' +
                jQuery.date(f.modified) +
                ' </div> <div class=""> <i class="fas fa-chart-line"></i> ' +
                f.hits +
                ' </div> </div> </div> <div class="" style="border-bottom-left-radius:8px; border-bottom-right-radius:8px; background: #201943 !important; border-top:1px solid #4d378e;padding:12px 10px;"> <div class="row"> <div class="col-6 mb-1">' + here_install_button + 
                " </div> " +
                here_download_button +
                " " +
                here_details_button +
                " " +
                here_support_button_just_link +
                " " +
                here_support_button +
                " " +
                here_report_button +
                " " +
                here_multiple_download_button +
                " " +
                here_virus_scan_button +  
                " " + 
                here_download_content_button + 
                "  </div> </div> </div></div>";

              if (j % final_show_num_of_items == 0) {
                plugin_list_data += '</div><div class="row">';
              }

              j++;
            });

            if (j % final_show_num_of_items != 0) {
              plugin_list_data += "</div>";
            }
            plugin_list_data += "</div>";
            jQuery("#list .wrapper").append(plugin_list_data);
          },
        });
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });
}





  function paginationDataController(ajax_search, links, page) {


    var pagination = $('#pagination');
    pagination.empty(); // Clear previous pagination links

    $.each(links, function (index, link) {
      var listItem = $('<li class="page-item"></li>');
      var linkElement = $('<a class="page-link"></a>');

      if (link.url) {
        linkElement.attr('href', link.url);
        linkElement.click(function (event) {
          event.preventDefault();
          var page = getPageFromUrl(link.url);
          handlePageClick(ajax_search, page);
        });


        // Add "active" class to the selected page link
        if (page === getPageFromUrl(link.url)) {
          linkElement.addClass('active');
        }

      } else {
        linkElement.addClass('disabled');
      }

    // Decode HTML entities
      var decodedLabel = $('<div>').html(link.label).text();
      linkElement.html(decodedLabel);

      listItem.append(linkElement);
      pagination.append(listItem);
    });
  }

  function getPageFromUrl(url) {
    var regex = /[?&]page=(\d+)/;
    var match = regex.exec(url);
    return match ? parseInt(match[1]) : null;
  }

  function handlePageClick(ajax_search, page) {
    // Replace this with your logic to handle the page click
    load_data(ajax_search, page);

  }

  var linksData = {
    // Place the "links" array from the JSON data here
  };

  paginationDataController(linksData);






function showToast() {
  var toast = document.querySelector('.toast');
  var toastEl = new bootstrap.Toast(toast);
  toastEl.show();
}


function add_to_cart_fv(d) {
    //$('#cart-dropdown').toggle();
    /*let dropdownMenu = document.querySelector('.dropdown-menu.cart-dropdown');
    if (!dropdownMenu.classList.contains('show')) {
      dropdownMenu.classList.add('show');
    }
*/

  var productId = d.getAttribute("data-id");
  var productName = d.getAttribute("data-itemname");

  // Get the cart items from the cookie or create a new empty object
  var cartData = getCartData();
  var cartCount = Object.keys(cartData).length;

   jQuery('.dropdown-menu.cart-dropdown')
      .css('display', 'block')
      .css('position', 'absolute');
    


  if(cartCount < 10){
    // Check if the product is already in the cart
    if (typeof cartData[productId] === 'undefined') {
      cartData[productId] = {
        'name': productName,
        'qty': 1
      };

      // Add the new item to the cart dropdown
      var cartItems = document.getElementById("cart-items");
      var newItem = document.createElement("div");
      newItem.innerHTML = productName + '<button type="button" class="btn btn-danger btn-sm float-right" onclick="remove_from_cart(this)" data-id="' + productId + '">Remove</button>';
      cartItems.appendChild(newItem);
    } else {
    }

    showToast();


  }else{
    alert('Sorry, Maximum limit is 10 items.')
  }
  // Save the updated cart data to the cookie
  setCartData(cartData);

  refreshCartDisplay();
};


function remove_from_cart(d) {
  var productId = d.getAttribute("data-id");

  // Get the cart items from the cookie or create a new empty object
  var cartData = getCartData();

  // Remove the selected item from the cart
  delete cartData[productId];

  // Remove the item from the cart dropdown
  var cartItems = document.getElementById("cart-items");
  var itemToRemove = d.parentNode;
  cartItems.removeChild(itemToRemove);

  // Save the updated cart data to the cookie
  setCartData(cartData);

};


  /*

    function getCartData() {
        var cartData = $.cookie('cartData');
        if (typeof cartData !== 'undefined') {
            cartData = JSON.parse(cartData);
        } else {
            cartData = {};
        }
        return cartData;
    }

    */

    function getCartData() {
        var cartData = $.cookie('cartData');
        if (typeof cartData !== 'undefined') {
            cartData = JSON.parse(cartData);
            var expires = new Date(cartData.expires);
            var now = new Date();
            if (now > expires) {
                // Cookie has expired, remove it
                $.removeCookie('cartData');
                cartData = {};
            }
        } else {
            cartData = {};
        }
        return cartData;
    }


    /*
    function setCartData(cartData) {
        $.cookie('cartData', JSON.stringify(cartData));
    }
    */

    function setCartData(cartData) {
        var expires = new Date();
        expires.setTime(expires.getTime() + (10 * 60 * 1000)); // set expiration time to 10 minutes
        $.cookie('cartData', JSON.stringify(cartData), { expires: expires });
    }





function refreshCartDisplay() {
  var cartData = getCartData();
  var cartCount = Object.keys(cartData).length;
  var cartItemsList = jQuery('.cart-dropdown .cart-items');
  

  // Clear cart items list
  cartItemsList.empty();

  // Add each item to the cart items list
  for (var itemId in cartData) {
    var itemName = cartData[itemId].name;
    var cartItem = '<li data-id="' + itemId + '" data-name="' + itemName + '">' + itemName + '<button class="btn btn-sm btn-danger remove-item float-end">Remove</button></li>';
    cartItemsList.append(cartItem);

  }
 
  // Update cart count
  jQuery('#cart-dropdown .cart-count').text('(' + cartCount + ')');

  // Get a reference to the dropdown menu element
  const dropdownMenu = document.querySelector('.dropdown-menu');

  // Get a reference to the button inside the dropdown menu
  const button = dropdownMenu.querySelector('button');

  // Add a click event listener to the button
  button.addEventListener('click', (event) => {
    // Prevent the default behavior of the button click
    event.preventDefault();
    
    // Stop the event propagation to prevent the dropdown menu from closing
    event.stopPropagation();



});


  // Bind click event handlers to Download and Install buttons
  jQuery('.cart-dropdown #clearall-button').off('click').on('click', function() {
    $.removeCookie('cartData');
    refreshCartDisplay();
  });




  // Bind click event handlers to Download and Install buttons
  jQuery('.cart-dropdown #download-button').off('click').on('click', function() {
    jQuery(".progress").hide();
    
    var progressBar = $('.progress');

    progressBar.attr('aria-valuenow', 0);
    progressBar.find('.progress-bar').css('width', 0 + '%');



    var cartItems = [];
    cartItemsList.children().each(function() {
      var itemId = jQuery(this).attr('data-id');
      var itemName = jQuery(this).attr('data-name');
      cartItems.push({
        'id': itemId,
        'name': itemName
      });
    });
    // Send AJAX request to download items






  var cartItemListForS = JSON.stringify(cartItems);

  var ajax_url = plugin_ajax_object.ajax_url;


  jQuery
    .ajax({
      data: { action: "fv_plugin_buttons_ajax_multiple", product_hash: cartItems },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if (json.result == "failed") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

          jQuery.alert({
            content: json.msg,
          });
        }
        if (json.length == 0) {
          jQuery.alert({
            content: "To enjoy this feature please activate your license.",
          });
        } else {
          collectortMultiple(json, 'download');
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });















  });

  jQuery('.cart-dropdown #install-button').off('click').on('click', function() {
    jQuery(".progress").hide();

    var progressBar = $('.progress');

    progressBar.attr('aria-valuenow', 0);
    progressBar.find('.progress-bar').css('width', 0 + '%');



    var cartItems = [];
    cartItemsList.children().each(function() {
      var itemId = jQuery(this).attr('data-id');
      var itemName = jQuery(this).attr('data-name');
      cartItems.push({
        'id': itemId,
        'name': itemName
      });
    });
    // Send AJAX request to install items



  var cartItemListForS = JSON.stringify(cartItems);

  var ajax_url = plugin_ajax_object.ajax_url;


  jQuery
    .ajax({
      data: { action: "fv_plugin_buttons_ajax_multiple", product_hash: cartItems },
      type: "POST",
      url: ajax_url,
      success: function (data) {
        var data_s = data.slice(0, -1);
        var json = JSON.parse(data_s);

        if (json.result == "failed") {
          setTimeout(function () {
            jQuery("#overlay").fadeOut(300);
          }, 500);

          jQuery.alert({
            content: json.msg,
          });
        }
        if (json.length == 0) {
          jQuery.alert({
            content: "To enjoy this feature please activate your license.",
          });
        } else {
          collectortMultiple(json, 'install');
        }
      },
    })
    .done(function () {
      setTimeout(function () {
        jQuery("#overlay").fadeOut(300);
      }, 500);
    });









  });

  // Bind click event handlers to Remove buttons
  jQuery('.cart-dropdown .remove-item').off('click').on('click', function() {
    var itemId = jQuery(this).parent().attr('data-id');
    var cartData = getCartData();
    delete cartData[itemId];
    setCartData(cartData);
    refreshCartDisplay();
  });

  if(cartCount > 0){
    $('#download-button').show();
    $('#install-button').show();
    $('#clearall-button').show();
    $('.cart-items-notfound').hide();

  }else{
    $('#download-button').hide();
    $('#install-button').hide();
    $('#clearall-button').hide();
    $('.cart-items-notfound').show();

  }

}



window.onload = function() {
  refreshCartDisplay();
};


jQuery(document).ready(function() {
  // Get the cart-get-dropdown and get-cart-dropdownsub elements
  var cartGetDropdown = $('.cart-get-dropdown');
  var getCartDropdownsub = $('.get-cart-dropdownsub');
  var addToBulkBtn = $('.add_to_bulk_btn');

  // Show/hide the dropdown when the cart-get-dropdown is clicked
  cartGetDropdown.click(function() {
    getCartDropdownsub.toggle();
  });

  // Hide the dropdown when the user clicks anywhere on the webpage except the cart-get-dropdown or the add_to_bulk_btn
  jQuery(document).on('click', function(event) {
    if (!cartGetDropdown.is(event.target) && cartGetDropdown.has(event.target).length === 0 && !$(event.target).hasClass('add_to_bulk_btn')) {
      getCartDropdownsub.hide();
    }
  });




  refreshCartDisplay();



});



function grab_dc_product_dcontents(d){
    jQuery("#overlay").fadeIn(300);　
    jQuery(".progress").hide();

    var product_hash = (d.getAttribute("data-id"));
 
      var ajax_url = plugin_ajax_object.ajax_url;
      jQuery.ajax({ 
           data: {action: 'fv_fs_plugin_dc_contents_ajax', product_hash:product_hash},
           type: 'POST',
           url: ajax_url,
           success: function(data) {
            console.log(data);
              var data_s = data.slice(0, -1);
              var json = JSON.parse(data_s);
              console.log(json);

              if (json.length === 0) {
                jQuery('#empModal').modal('hide');

                jQuery.alert({
                    content: 'No contents found.',
                });
              } else {
                


                var button_data = '<table class="table table-bordered data_table998fv" style="color:#fff; border:1px solid #fff;"> <thead> <tr> <th class="text-white">File name</th> <th class="text-white">Type</th> <th class="text-white">Date</th> <th class="text-white">Action</th> </tr> </thead> <tbody>';
                var getIdFromContent = null;
                jQuery.each(json, function(index, item) {
                    if(item){
                        
                    
                  var ind_item = (item);
                  const dateTimeStringFvDc = ind_item.created_at;
                  const datePartFvDC = dateTimeStringFvDc.split(" ")[0];



                  const inputStringfvdc = ind_item.content_type;

                  // Step 1: Remove the hyphen and split the string into an array
                  const wordsArrayfvdc = inputStringfvdc.split("-");

                  // Step 2: Capitalize each word in the array
                  const capitalizedWordsArrayfvdc = wordsArrayfvdc.map((word) => {
                    return word.charAt(0).toUpperCase() + word.slice(1);
                  });

                  // Step 3: Join the words back together to form the final string
                  const resultStringFvDc = capitalizedWordsArrayfvdc.join("");





                  if(getIdFromContent == null){
                    getIdFromContent = ind_item.id;
                  }
                  button_data += '<tr class="text-white">';
                  button_data += '<td class="text-white">';
                    button_data += ind_item.content_name;
                  button_data += '</td>';
                  button_data += '<td class="text-white">';
                    button_data += resultStringFvDc;
                    button_data += '</td>';
                    button_data += '<td class="text-white">';
                  button_data += datePartFvDC;
                    button_data += '</td>';
                    button_data += '<td class="text-white">';

                  button_data += '<button id="dc_option"  onclick="grab_dc_product_hash(this);" data-dltype="single" data-id="'+ind_item.id+'" onclick="grab_product_dowload_link_dc(this); this.disabled=true;" class="btn btn-success btn-xs text-white download_dc_fv" style="padding: 1px 7px 1px 0px; font-size: 12px;"> &nbsp; Download </button>';
                  button_data += '</td>';
                  button_data += '</tr>';
                    }
                });
                  button_data += '</table>';
                  button_data += '<table class="table" style="color:#fff;margin-top:20px;"> <tr> <td colspan><button class="btn btn-success btn-xs text-white download_dc_fv" onclick="grab_dc_product_hash(this);" data-dltype="all_dl" data-id="'+getIdFromContent+'" style="width:100%;"> Download all </button></td </tr> </table>';
              
                jQuery('.modal-body').html(button_data);
                jQuery('#empModal').modal('show');

//


                  $('.data_table998fv').DataTable({
                      "pageLength": 10
                  });





              }






            setTimeout(function(){
              jQuery("#overlay").fadeOut(300);
            },500);

              
          }
      }).done(function() {
        setTimeout(function(){
          jQuery("#overlay").fadeOut(300);
        },500);
      });
}

//demo contents product button web
function grab_dc_product_hash(d){
    jQuery("#overlay").fadeIn(300);　

    var product_hash = (d.getAttribute("data-id"));
    var data_dltype = (d.getAttribute("data-dltype"));
    var product_mfile_id = '';

 
    jQuery(".cs-fp-follow-button").click();
      var ajax_url = plugin_ajax_object.ajax_url;
      jQuery.ajax({ 
           data: {action: 'fv_fs_plugin_dc_buttons_ajax', product_hash:product_hash, data_dltype:data_dltype},
           type: 'POST',
           url: ajax_url,
           success: function(data) {
              var data_s = data.slice(0, -1);
              var json = JSON.parse(data_s);
              console.log(json);


               if(data_s == null || data_s.length == 5){
                jQuery.alert({
                    content: 'No downloadable file is available for this item. Please try again later',
                });
              }else{               
               
                
                   if(json.result == 'invalid'){
                        setTimeout(function(){
                          jQuery("#overlay").fadeOut(300);
                        },500);

                        if(json.msg == 'Please login to download.'){
                          window.location="/get-started/";
                        }else{

                          jQuery.alert({
                              content: json.msg,
                          });
                        }

                  }
                  

              if(data_s.length == 0){
                jQuery.alert({
                    content: 'To enjoy this feature please activate your license.',
                });
              }else{
                collectortdc(json);
              }

              
              }



              
          }
      }).done(function() {
        setTimeout(function(){
          jQuery("#overlay").fadeOut(300);
        },500);
      });
  }






function collectortdc(json){
  var button_data = '<div class="row">';

  jQuery.each(json, function(index, item) {
      if(item){
          
      
    var ind_item = JSON.parse(item);
    button_data += '<div class="col-md-6"><div class="card mb-2 bg-light" style="min-width:100%;">';
    button_data += '<div class="card-header">';
      button_data += ind_item.plan_name;
    button_data += '</div>';
    button_data += '<ul class="list-group list-group-flush">';
      button_data += '<li class="list-group-item">Plan Type<b>: '+ind_item.plan_type.toUpperCase()+'</b></li>';
      button_data += '<li class="list-group-item">Plan Limit: '+ind_item.plan_limit+'</li>';
      button_data += '<li class="list-group-item">Available Limit: '+(ind_item.download_available)+'</li>';
    button_data += '</ul>';
    button_data += '<div class="card-footer"><button style="width:100%;" id="dc_option" data-license="'+ ind_item.license_key+'" data-id="'+ind_item.product_hash+'" data-dltype="'+ind_item.dl_type+'" onclick="grab_product_dowload_link_dc(this); this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-arrow-down"></i>  &nbsp; Download from '+ind_item.plan_type.toUpperCase()+' plan </button> </div> ';
    button_data += '</div>';
    button_data += '</div>';
      }
  });
    button_data += '</div>';

      jQuery('.modal-body').html(button_data);
      jQuery('#empModal').modal('show');
      setTimeout(function(){
        jQuery("#overlay").fadeOut(300);
      },500);

}








function collectort(json) {

  var button_data = '<div class="row">';

  jQuery.each(json, function (index, item) {
    var ind_item = JSON.parse(item);

    let count_versions = (ind_item.other_available_versions.length);
    let whichevent = 'onChange';
    if(count_versions == 1){
      whichevent = 'onClick';

    }
    button_data +=
      '<div class="col"><div class="card bg-light" style="min-width:100%;"> ';
    button_data += '<div class="card-header">';
    button_data += ind_item.plan_name;
    button_data += "</div>";
    button_data += '<ul class="list-group list-group-flush">';
    button_data +=
      '<li class="list-group-item">Plan Type<b>: ' +
      ind_item.plan_type.toUpperCase() +
      "</b></li>";
    button_data +=
      '<li class="list-group-item">Plan Limit: ' +
      ind_item.plan_limit +
      "</li>";
    button_data +=
      '<li class="list-group-item">Available Limit: ' +
      ind_item.download_available +
      "</li>";
    button_data += "</ul>";
    button_data += "</div>";
    button_data +=
      '<button id="option1" data-license="' +
      ind_item.license_key +
      '" data-id="' +
      ind_item.product_hash +
      '" onclick="grab_product_dowload_link(this); this.disabled=true;" class="btn btn-sm btn-block card-btn"><i class="fa fa-download"></i>Download ' +
      
      " LATEST VERSION </button> ";

      button_data += '<div class="row" style="margin-top:40px;">';

        button_data += '<div class="col">';
          button_data += '<table class="table table-bordered" style="color:#fff;">';
                  button_data += "<tr>";
                    button_data += "<td>";

                      button_data += '<div class="input-group text-white">';
                      button_data += '<label class="input-group-text" for="inputGroupSelect01">Please choose your preferred version. Once selected, it will be installed and activated automatically</label>';
                      button_data += '<select '+whichevent+'="grab_product_dowload_link(this); this.disabled=true;" class="form-select text-white '+ind_item.license_key+ind_item.product_hash+'" name="downloadOtherVerions">';
                      
                      /*
                      jQuery.each(ind_item.other_available_versions.reverse(), function (index2, item2) {

                        button_data += '<option value="'+item2.generated_version+'" data-key="'+item2.filename+'" data-license="' +
                                                ind_item.license_key +
                                                '" data-id="' +
                                                ind_item.product_hash +
                                                '" >Version '+item2.generated_version+'</option>';

                      });

                      */
                      if (Array.isArray(ind_item.other_available_versions)) {
                        jQuery.each(ind_item.other_available_versions.reverse(), function (index2, item2) {
                          button_data += '<option value="'+item2.generated_version+'" data-key="'+item2.filename+'" data-license="' +
                            ind_item.license_key +
                            '" data-id="' +
                            ind_item.product_hash +
                            '" >Version '+item2.generated_version+'</option>';
                        });
                      }




                      button_data += '</select>';

                      /*button_data +=  '<button id="option1" data-license="' +
                                        ind_item.license_key +
                                        '" data-id="' +
                                        ind_item.product_hash +
                                        '" onclick="grab_product_dowload_link(this); this.disabled=true;" class="btn btn-outline-secondary card-btn"><i class="fa fa-download"></i>Download from ' +
                                        ind_item.plan_type.toUpperCase() +
                                        " plan </button> </div>";*/
                    button_data += "</td>";
                  button_data += "</tr>";


          button_data += "</table>";
        button_data += "</div>";
      button_data += "</div>";



    button_data += "</div>";



  });
  button_data += "</div>";




    



  jQuery(".modal-body").html(button_data);
  jQuery("#empModal").modal("show");
  setTimeout(function () {
    jQuery("#overlay").fadeOut(300);
  }, 500);
}



   function getFileNameFromURL(url) {
      // Get the part of the URL after the last '/'
      var filenameWithParams = url.substring(url.lastIndexOf('/') + 1);

      // Remove query parameters from the filename (everything after the '?')
      var filenameWithoutParams = filenameWithParams.split('?')[0];

      // Extract the file name and extension
      var parts = filenameWithoutParams.split('.');
      var extension = parts.pop();
      var filename = parts.join('.');

      filenameFinal = filename+extension;
      console.log(filenameFinal);
      return filenameFinal;
      //return { filename: filename, extension: extension };
    }


    function downloadFileDC(url) {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', url, true);
      xhr.responseType = 'blob';

      xhr.onload = function () {
        if (xhr.status === 200) {
          var blob = xhr.response;
          var filename = getFileNameFromURL(url); // Use the getFileNameFromURL function to extract the filename
          var a = document.createElement('a');
          a.href = window.URL.createObjectURL(blob);
          a.download = filename;
          a.style.display = 'none';
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(a.href);
        }
      };

      xhr.send();
    }



  function grab_product_dowload_link_dc(d){
    jQuery("#overlay").fadeIn(300);　

    var plugin_download_hash = (d.getAttribute("data-id"));
    var license_key = (d.getAttribute("data-license"));
    var download_type = (d.getAttribute("data-dltype"));
    var ajax_url = plugin_ajax_object.ajax_url;

      jQuery.ajax({ 
           data: {action: 'fv_fs_plugin_download_ajax_dc', plugin_download_hash:plugin_download_hash, license_key:license_key, download_type:download_type},
           type: 'POST',
           url: ajax_url,
           success: function(data) {

              var data_s = data.slice(0, -1);
              var json = JSON.parse(data_s);

              console.log(json);
              if(json.result == 'success'){
              jQuery('#'+license_key+' #plan_limit_id').html( json.plan_limit );
              jQuery('#'+license_key+' #current_limit_id').html( json.download_current_limit );
              jQuery('#'+license_key+' #limit_available_id').html( json.download_available + ' / ');

                if(download_type == 'single'){
                    downloadFileDC(json.link);
                }else{
                    location.href = json.link;
                }

                jQuery('#empModal').modal('hide'); 
              }else{
                jQuery('#empModal').modal('hide'); 
          
                  if(json.msg){
                    jQuery.alert({
                        title: 'Alert!',
                        content: json.msg,
                    });
                  }else{
                    jQuery.alert({
                        title: 'Alert!',
                        content: 'Something went wrong, Please try again later!',
                    });
                  }
                
              }
          }
      }).done(function() {
        setTimeout(function(){
          jQuery("#overlay").fadeOut(300);
        },500);
      });

  }
 





