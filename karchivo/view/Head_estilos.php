<style>
	.sidenav {
		height: 100%;
		width: 0;
		position: fixed;
		z-index: 1;
		top: 0;
		left: 0;
		/*  background-color: #111;*/
		overflow-x: hidden;
		transition: 0.5s;
		padding-top: 60px;
		font-size: 11px;
	}

	.sidenav a {
		padding: 8px 8px 8px 32px;
		text-decoration: none;
		font-size: 11px;
		color: #322E2E;
		display: block;
		transition: 0.3s;
	}

	.sidenav a:hover,
	.offcanvas a:focus {
		color: #BFBFBF;
	}

	.sidenav .closebtn {
		position: absolute;
		top: 0;
		right: 25px;
		font-size: 11px;
		margin-left: 50px;
	}

	#main {
		transition: margin-left .5s;
		padding: 16px;
	}

	@media screen and (max-height: 450px) {
		.sidenav {
			padding-top: 15px;
		}

		.sidenav a {
			font-size: 11px;
		}


	}

	body {
		font-style: normal;
		font-weight: normal;
		font-size: 12px;
		color: #000000;
	}

	#mdialTamanio {
		width: 65% !important;
	}

	#mdialTamanio1 {
		width: 85% !important;
	}

	#mdialTamanio2 {
		width: 65% !important;
	}


	#global {
		height: 350px;
		width: 750px;
		border: 1px solid #ddd;
		background: #f1f1f1;
		overflow-y: scroll;
	}

	#casillero {
		width: 10px;
		padding: 2px;
	}
</style>
