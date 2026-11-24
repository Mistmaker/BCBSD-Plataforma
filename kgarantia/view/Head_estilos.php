<style>
	 
 body {
			font-style: normal;
			font-weight: normal;
			font-size: 12px;
}

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
    color:#322E2E;
    display: block;
    transition: 0.3s;
}

.sidenav a:hover, .offcanvas a:focus{
    color:#BFBFBF;
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
  .sidenav {padding-top: 15px;}
  .sidenav a {font-size: 11px;}
}
	 
	  .btn-flotante {
	font-size: 12px; /* Cambiar el tamaño de la tipografia */
	text-transform: uppercase; /* Texto en mayusculas */
	font-weight: bold; /* Fuente en negrita o bold */
	color: #ffffff; /* Color del texto */
	border-radius: 5px; /* Borde del boton */
	letter-spacing: 2px; /* Espacio entre letras */
	background-color: #E91E63; /* Color de fondo */
	padding: 18px 30px; /* Relleno del boton */
	position: fixed;
	bottom: 40px;
	right: 40px;
	transition: all 300ms ease 0ms;
	box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
	z-index: 99;
}
.btn-flotante:hover {
	background-color: #3a3be9; /* Color de fondo al pasar el cursor */
	box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.3);
	transform: translateY(-7px);
	color: #ffffff; /* Color del texto */
}
@media only screen and (max-width: 600px) {
 	.btn-flotante {
		font-size: 14px;
		padding: 12px 20px;
		bottom: 20px;
		right: 20px;
	}
}
	 
	 	 
	 @media screen and (max-width: 600px) {
    .table {
        border: 0px;
    }
    .table caption {
        font-size: 22px;
    }
    .table thead {
        display: none;
    }
    .table tr {
        margin-bottom: 8px;
        border-bottom: 4px solid #ddd;
        display: block;
    }
    .table th, .table td {
        font-size: 12px;
    }
    .table td {
        display: block;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    .table  td:last-child {
        border-bottom: 0px;
    }
    .table td::before {
        content: attr(data-label);
        font-weight: bold;
        text-transform: uppercase;
        float: left;
    }
}
 	 
	 #mdialTamanio{
  					width: 75% !important;
		}
  		 
	 
	 #mdialTamanio1 {
  					width: 75% !important;
		}
  		 
	 
	  .highlight {
 			 background-color: #FF9;
	   }
	  .de {
  			 background-color:#c3e1fb;
	  }
	  .ye {
  			 background-color:#93ADFF;
	  }
	 .di {
  			 background-color:#F5C0C1;
	  }
	 
	  
	  
</style>
