jQuery(function ($) {

  'use strict';

  /**
   * Toggle upload area.
   */

  /**
 * Move toolbar into WPvivid area.
 */
  function bbmwpvMoveToolbar() {

    let toolbar = $('#bbmwpv-toolbar');

    if (!toolbar.length) {
      return;
    }

    let target = $('#poststuff');

    if (!target.length) {
      return;
    }

    target.after(toolbar);

    toolbar.show();

    toolbar.css({
      margin: '15px 0'
    });
  }

  setTimeout(
    bbmwpvMoveToolbar,
    500
  );


  $(document).on('click', '#bbmwpv-bulk-upload-open', function () {

    $('#bbmwpv-upload-area').slideToggle(150);
  });

  /**
   * Bulk download.
   */
  $(document).on('click', '#bbmwpv-bulk-download', function () {

    let files = [];

    $('input[name="check_backup"]:checked').each(function () {

      let row = $(this).closest('tr');

      let downloadText = row.find(
        '[onclick*="wpvivid_initialize_download"]'
      ).text();

      let match = downloadText.match(/\((.*?)\)/);

      if (match && match[1]) {

        let sizeText = match[1];

        // console.log(sizeText);
      }

      let backupId = $(this).val();

      if (backupId) {
        files.push(backupId);
      }
    });

    if (!files.length) {

      alert('Please select backup files.');

      return;
    }

    let button = $(this);
    let progress = $('#bbmwpv-progress');

    if (!progress.length) {

      $('#bbmwpv-toolbar').after(

        '<div id="bbmwpv-progress" style="margin:15px 0;max-width:400px;">' +

        '<div style="' +
        'background:#dcdcde;' +
        'height:18px;' +
        'border-radius:9px;' +
        'overflow:hidden;' +
        '">' +

        '<div id="bbmwpv-progress-bar" style="' +
        'width:5%;' +
        'height:18px;' +
        'background:#2271b1;' +
        'transition:width .3s ease;' +
        '"></div>' +

        '</div>' +

        '<p id="bbmwpv-progress-text" style="margin-top:8px;">' +
        'Creating ZIP bundle...' +
        '</p>' +

        '</div>'
      );
    }

    $('#bbmwpv-progress-bar').css('width', '30%');

    button.prop('disabled', true);

    $.ajax({

      url: bbmwpv.ajax_url,
      type: 'POST',
      timeout: 0,

      data: {
        action: 'bbmwpv_bulk_download',
        nonce: bbmwpv.nonce,
        files: files
      },

      success: function (response) {

        button.prop('disabled', false);

        if (!response.success) {

          alert(response.data.message || 'Download failed.');

          return;
        }

        if (!response.data.url) {
          return;
        }

        $('#bbmwpv-progress-bar').css(
          'width',
          '100%'
        );

        $('#bbmwpv-progress-text').html(

          'Download should begin automatically.<br><br>' +

          '<div id="bbmwpv-manual-download" style="display:none;">' +

          '<a href="' +

          (
            response.data.fallback_url
              ? response.data.fallback_url
              : response.data.url
          ) +

          '" class="button button-primary" ' +
          'target="_blank">' +

          'Download ZIP Manually' +

          '</a>' +

          '</div>'
        );

        /**
         * Automatic download.
         */
        setTimeout(function () {

          let url = response.data.url;

          try {
            url = url.replace(
              /^http:/i,
              'https:'
            );
          } catch (e) { }

          window.location.href = url;

        }, 2000);

        /**
         * Show manual button only if needed.
         */
        setTimeout(function () {

          $('#bbmwpv-manual-download')
            .fadeIn(200);

        }, 5000);

        /**
         * Auto close later.
         */
        setTimeout(function () {

          $('#bbmwpv-progress').fadeOut(
            300,
            function () {
              $(this).remove();
            }
          );

        }, 20000);

      },

      error: function (xhr) {

        button.prop('disabled', false);

        console.log(xhr);

        $('#bbmwpv-progress-text').text(
          'Download failed.'
        );

        alert('Ajax request failed.');
      }
    });
  });

  /**
   * Upload backup bundle.
   */
  $(document).on('submit', '#bbmwpv-upload-form', function (e) {

    e.preventDefault();

    let formData = new FormData();

    let fileInput = $('#bbmwpv-upload-file')[0];

    if (!fileInput.files.length) {

      alert('Please select ZIP file.');

      return;
    }

    formData.append(
      'action',
      'bbmwpv_bulk_upload'
    );

    formData.append(
      'nonce',
      bbmwpv.nonce
    );

    formData.append(
      'file',
      fileInput.files[0]
    );

    let status = $('#bbmwpv-upload-result');

    status.html('<p>Uploading...</p>');

    $.ajax({

      url: bbmwpv.ajax_url,
      type: 'POST',
      data: formData,

      processData: false,
      contentType: false,

      success: function (response) {

        if (!response.success) {

          status.html(
            '<div class="notice notice-error"><p>' +
            response.data.message +
            '</p></div>'
          );

          return;
        }

        let html =
          '<div class="notice notice-success">' +
          '<p>' +
          response.data.message +
          '</p>';

        if (response.data.imported) {

          html += '<ul>';

          response.data.imported.forEach(function (file) {

            html += '<li>' + file + '</li>';
          });

          html += '</ul>';
        }

        html += '</div>';

        status.html(html);
      },

      error: function () {

        status.html(
          '<div class="notice notice-error">' +
          '<p>Upload failed.</p>' +
          '</div>'
        );
      }
    });
  });

});