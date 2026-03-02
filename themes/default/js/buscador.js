$(document).ready(() => {
   $('span[role=button][data-select]').on('click', function() {
      const button = $(this).data('select');
      $('span[role=button][data-select]').removeClass('active');
      $(this).addClass('active');
      $('input[name=engine]').attr({
         name: button
      });
   })
});