var sales_commission_agent_table;
$(document).ready(function () {
      //Sales commission agent
      sales_commission_agent_table = $('#sales_commission_agent_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/sales-commission-agents',
            columnDefs: [
                  {
                        targets: 2,
                        orderable: false,
                        searchable: false,
                  },
            ],
            columns: [
                  { data: 'full_name' },
                  { data: 'email' },
                  { data: 'contact_no' },
                  { data: 'address' },
                  { data: 'cmmsn_percent' },
                  { data: 'global_commission' },
                  { data: 'action' },
            ],
      });

      $(document).on('click', '#edit_commsn_agnt_button', function (e) {
            e.preventDefault();
            var container = $($(this).data('container'));
            $.ajax({
                  url: $(this).data('href'),
                  dataType: 'html',
                  success: function (result) {
                        container.html(result).modal('show');
                  }
            });
      });
      $('div.commission_agent_modal').on('shown.bs.modal', function (e) {
            $('form#sale_commission_agent_form').submit(function (e) {
                  e.preventDefault();
            }).validate({
                  submitHandler: function (form) {
                        var data = $(form).serialize();
                        $.ajax({
                              method: $(form).attr('method'),
                              url: $(form).attr('action'),
                              dataType: 'json',
                              data: data,
                              success: function (result) {
                                    if (result.success) {
                                          $('div.commission_agent_modal').modal('hide');
                                          toastr.success(result.msg);
                                          sales_commission_agent_table.ajax.reload();
                                    } else {
                                          toastr.error(result.msg);
                                    }
                              }
                        });
                  }
            });
      });

      $(document).on('click', 'button.delete_commsn_agnt_button', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                sales_commission_agent_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
});
