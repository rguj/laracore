
'use strict';

const config = {
	
	
	
	
	
	swal2: function(mode, override = {}) {
		var m = {
			simple: {
				title: '',
				text: '',
				icon: 'info',
				allowOutsideClick: false,
			},
			dialog: {
				title: '',
				text: '',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes',
				cancelButtonText: 'No',
				allowOutsideClick: false,
			},
		};
		var middleMerge = {
			customClass: 'swal2-custom'
		};		
		return PHP.array_merge(m[mode] || {}, middleMerge,  override);
	},
	
	
	
	
	
	toastr: function(mode, override = {}) {
		var m = {
			simple: {
				"closeButton": false,
				"debug": false,
				"newestOnTop": true,
				"progressBar": false,
				"positionClass": globals.toastr.positionClass,
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "5000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut",
			},
		};		
		var middleMerge = {
			customClass: 'swal2-custom'
		};		
		return PHP.array_merge(m[mode] || {}, middleMerge, override);
	}
	
	
	
	
	
}, CONFIG = config;