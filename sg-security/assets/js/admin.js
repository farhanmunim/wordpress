/*global ajaxurl*/
;(function( $ ) {
	$(document).ready(function() {
		$('.dismiss-sg-security-notice').on('click', function(e) {
			let $this = $(this);
			$.ajax(
				$this.data('link')
			)
			.success(function() {
				$this.parents('.notice-error').remove()
			})

		})
	})
})( jQuery )