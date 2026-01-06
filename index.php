<?php
session_start();
include 'conexion.php';

if (empty($_SESSION['grupo']) && !empty($_SESSION['family_group_code'])) {
    $stmt = $conectar->prepare(
      "SELECT grupo 
         FROM usuario 
        WHERE family_group_code = ? 
        LIMIT 1"
    );
    $stmt->bind_param('s', $_SESSION['family_group_code']);
    $stmt->execute();
    $stmt->bind_result($dbGrupo);
    if ($stmt->fetch()) {
        $_SESSION['grupo'] = $dbGrupo;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Proyecto: Servicios en la Nube">
    <meta name="author" content="TDAW">
    <title>Web | Proyecto de registro de datos</title>
   
    <!-- font icons -->
    <link rel="stylesheet" href="assets/vendors/themify-icons/css/themify-icons.css">

    <link rel="stylesheet" href="assets/vendors/animate/animate.css">

    <!-- Bootstrap + FoodHut main styles -->
    <link rel="stylesheet" href="assets/css/foodhut.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body data-spy="scroll" data-target=".navbar" data-offset="40" id="home">
    <!-- Navbar -->
    <nav class="custom-navbar navbar navbar-expand-lg navbar-dark fixed-top" data-spy="affix" data-offset-top="10">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#home">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">¿Pionix?</a></li>
                <li class="nav-item"><a class="nav-link" href="#objetivo">Objetivo</a></li>
                <li class="nav-item"><a class="nav-link" href="#blog">V, M & V</a></li>
                <!--<li class="nav-item"><a class="nav-link" href="#book-table">Redes</a></li>-->
            </ul>
            <a class="navbar-brand m-auto">
                <img src="assets/imgs/logo.png" class="brand-img" alt="Logo de ESCOM">
                <span class="brand-txt">Impulsando el futuro</span>
            </a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="CRUD/index.php" class="btn btn-primary ml-xl-4">Documentación</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Encabezado -->
    <header id="home" class="header">
        <div class="overlay text-white text-center">
            <h1 class="display-2 font-weight-bold my-3">Pionix</h1>
            <h2 class="display-4 mb-5 ">"Impulsando el futuro"</h2>
        </div>
    </header>

    <!-- Sección  ESCOM-->
    <div id="about" class="container-fluid wow fadeIn" data-wow-duration="1.5s">
        <div class="row">
            <div class="col-lg-6 has-img-bg"></div>
            <div class="col-lg-6">
                <div class="row justify-content-center text-justify">
                    <div class="col-sm-10 py-5 my-5">
                        <h2 class="mb-4">¿Quiénes somos?</h2>

                        <p>En un contexto donde las instituciones educativas y organizaciones dependen cada vez más de la disponibilidad, seguridad y escalabilidad de sus servicios de Tecnologías de la Información, surge la necesidad de modernizar la infraestructura tecnológica para responder de manera eficiente a las demandas actuales y futuras. El servicio de Migración e Implementación de Servicios en la Nube se concibe como una solución estratégica orientada a garantizar la continuidad operativa, mejorar la calidad del servicio y optimizar los recursos tecnológicos de la institución.</p>
                        
                        <p>Este servicio tiene como propósito trasladar y estabilizar los servicios críticos —como aplicaciones institucionales, almacenamiento, autenticación y correo electrónico— desde una infraestructura tradicional on-premise hacia entornos de nube pública o híbrida, aprovechando modelos IaaS, PaaS y SaaS. La adopción de esta tecnología permite incrementar la disponibilidad, flexibilidad y seguridad de los sistemas, al tiempo que reduce la deuda técnica y los costos asociados al mantenimiento de infraestructura física.</p>
                        
                        <p>La implementación del servicio se apoya en buenas prácticas de Gobierno de TI, incorporando acuerdos de niveles de servicio (SLA) y acuerdos operativos internos (OLA) que aseguran tiempos de respuesta adecuados, alta disponibilidad (≥ 99.9%), respaldo de la información, monitoreo continuo y una atención oportuna a incidentes. Asimismo, el servicio se encuentra alineado con los objetivos estratégicos definidos en el Balanced Scorecard, contribuyendo a la optimización de costos, mejora de la experiencia del usuario, eficiencia de los procesos internos y fortalecimiento de las capacidades del personal de TI.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Espacio 1  -->
    <div class="container-fluid has-bg-overlay has-height-xs" ></div>

    <!-- Sección Objetivo -->
    <div id="objetivo" class="container-fluid wow fadeIn" data-wow-duration="1.5s" style="padding-top: 80px; margin-top: -80px;">
    <div class="row">
        <div class="col-lg-6">
            <div class="row justify-content-center text-justify">
                <div class="col-sm-10 py-5 my-5">
                    <h2 class="mb-4">Nuestro Objetivo</h2>
                    <p>Migrar y estabilizar los servicios críticos de la institución a una plataforma de nube pública o híbrida en un periodo aproximado de seis meses, garantizando una disponibilidad mínima del 99.9% para los servicios productivos y reduciendo el tiempo de aprovisionamiento de entornos tecnológicos de semanas a solo días.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 has-img-bg" style="background-image: url('assets/imgs/objetivo.jpg'); background-size: cover; background-position: center;"></div>
    </div>
</div>

    <!-- Espacio 2  -->
    <div class="container-fluid has-bg-overlay has-height-xs" ></div>

<!-- BLOG Section  -->
<div id="blog" class="container-fluid bg-dark text-light py-5 text-center wow fadeIn">
    <h2 class="section-title">V, M & V</h2>
    <div class="row justify-content-center">
        <div class="col-sm-7 col-md-4 mb-3">
            <ul class="nav nav-pills nav-justified mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#foods" role="tab" aria-controls="pills-home" aria-selected="true">Visión</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#juices" role="tab" aria-controls="pills-profile" aria-selected="false">Misión</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-valores-tab" data-toggle="pill" href="#valores" role="tab" aria-controls="pills-valores" aria-selected="false">Valores</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="tab-content" id="pills-tabContent">
        <!-- Pestaña Visión -->
        <div class="tab-pane fade show active" id="foods" role="tabpanel" aria-labelledby="pills-home-tab">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card bg-transparent border p-3">
                        <div class="row align-items-center">
                            <!-- Columna del texto -->
                            <div class="col-md-8 text-left">
                                <h4 class="pt-2 pb-2">Nuestra visión es...</h4>
                                <p class="text-white text-center">
                                    Ser la compañía de TI más valorada por su talento humano altamente calificado y por nuestra capacidad de anticipar las necesidades tecnológicas del mercado y creando soluciones de futuro.
                                </p>
                            </div>
                            <!-- Columna de la imagen -->
                            <div class="col-md-4">
                                <img src="assets/imgs/blog-1.png" alt="template by DevCRID http://www.devcrud.com/" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña Misión -->
        <div class="tab-pane fade" id="juices" role="tabpanel" aria-labelledby="pills-profile-tab">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card bg-transparent border p-3">
                        <div class="row align-items-center">
                            <!-- Texto -->
                            <div class="col-md-8 text-left">
                                <h4 class="pt-2 pb-2">Nuestra misión es...</h4>
                                <p class="text-white text-center">
                                    Proveer soluciones y servicios de tecnología de la información innovadores y eficientes que impulsen la competitividad y el crecimiento sostenible de nuestros clientes.
                                </p>
                            </div>
                            <!-- Imagen -->
                            <div class="col-md-4">
                                <img src="assets/imgs/blog4.png" alt="Club Artístico" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>   
            </div>
        </div>

        <!-- Pestaña Valores -->
        <div class="tab-pane fade" id="valores" role="tabpanel" aria-labelledby="pills-valores-tab">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card bg-transparent border p-3">
                        <div class="row align-items-center">
                            <!-- Texto -->
                            <div class="col-md-8 text-left">
                                <h4 class="pt-2 pb-2">Nuestros valores...</h4>
                                <p class="text-white text-center">
                                    Nuestros valores se fundamentan en: la Innovación para estar siempre a la vanguardia; la Integridad como base de la confianza; la Orientación al Cliente garantizando su satisfacción; la Excelencia en cada entrega; y el Trabajo en Equipo como motor de nuestro éxito.
                                </p>
                            </div>
                            <!-- Imagen -->
                            <div class="col-md-4">
                                <img src="assets/imgs/valores.jpg" alt="Valores" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>   
            </div>
        </div>

    </div>
</div>

    <!-- Espacio 3  -->
    <div class="container-fluid has-bg-overlay has-height-xs" ></div>

    <!--  gallary Section  
    <div id="gallary" class="text-center bg-dark text-light has-height-md middle-items wow fadeIn">
        <h2 class="section-title">Sitios de interés</h2>
    </div>
    <div class="gallary row">
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-1.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon ">Salón</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-2.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon">Explanada</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-3.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon">Lab Electronica</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-4.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon">Lab Cómputo</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-5.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon"> Barra de café</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-6.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon"> Área Verde</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-7.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon"> Biblioteca </i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-8.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon"> Palapas </i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-9.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon"> Canchas </i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-10.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon">Papelería</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-11.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon">Ed. Gobierno</i>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 gallary-item wow fadeIn">
            <img src="assets/imgs/gallary-12.jpg" alt="template by DevCRID http://www.devcrud.com/" class="gallary-img">
            <a href="#" class="gallary-overlay">
                <i class="gallary-icon"> Auditorio </i>
            </a>
        </div>
    </div>-->

    <!-- Sección Redes  -->
    <!--
    <div class="container-fluid has-bg-overlay text-center text-light has-height-lg middle-items" id="book-table">
        <div class="">
            <h2 class="section-title mb-5 my-5 font-weight-bold"> <i class="ti-facebook ti-35px"></i> Redes Institucionales <i class="ti-twitter ti-35px"></i> </h2>
            <div class="row justify-content-center mb-5">
                <div class="panelr col-md-6 my-3 my-md-0">
                    <h3 class="mb-4 font-weight-bolder">Facebook</h3>
                    <div class="fb-page" data-href="https://www.facebook.com/escomipnmx" data-tabs="timeline" data-width="500" data-height="600" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                        <blockquote cite="https://www.facebook.com/escomipnmx" class="fb-xfbml-parse-ignore">
                            <a href="https://www.facebook.com/escomipnmx">Facebook by ESCOM</a>
                        </blockquote>
                    </div> <script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v11.0" nonce="tu_nonce"></script> 
                </div>
                <div class="panelr col-md-6 my-3 my-md-0">
                    <h3 class="mb-3 text-center font-weight-bolder">Twitter</h3>
                    <center><a class="twitter-timeline" data-width="500" data-height="600" href="https://twitter.com/escomunidad">Tweets by ESCOM</a> </center>
                    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                </div>
            </div>
            <div class="row justify-content-center mb-5">
                    <div class="icono col-md-6 my-5 my-md-0">
                        <h2><a href="https://www.facebook.com/escomipnmx/" title="Facebook-ESCOM"><i class="ti-facebook"></i></a><br>/ESCOM IPN</h2>
                    </div>
                    <div class="icono col-md-6 my-5 my-md-0">
                        <h2><a href="http://www.twitter.com/escomunidad/" title="Twitter-ESCOM"><i class="ti-twitter"></i></a><br>@escomunidad</h2>
                    </div>
                       
            </div>
        </div>
    </div> -->

    <!-- CONTACT Section
    <div id="contact" class="container-fluid bg-dark text-light border-top wow fadeIn">
        <div class="row">
            <div class="col-md-6 px-0">
                <div id="map" style="width: 100%; height: 100%; min-height: 400px"></div>
            </div>
            <div class="col-md-6 px-5 has-height-lg middle-items">
                <h3>Mapa</h3>
                <p>La Escuela Superior de Cómputo (ESCOM) del Instituto Politécnico Nacional (IPN) se encuentra ubicada en el campus Zacatenco, en la zona norte de la 
                    Ciudad de México. El edificio de la ESCOM es una estructura moderna y distintiva, con amplias instalaciones diseñadas para crear un entorno propicio para el aprendizaje y la investigación en el campo de la computación.</p>
                <div class="text-muted">
                    <p><span class="ti-location-pin pr-3"></span> Av. Juan de Dios Bátiz s/n, Unidad Profesional "Adolfo López Mateos". Col. Lindavista, C.P. 07738</p>
                    <p><span class="ti-support pr-3"></span> (123) 456-7890</p>
                    <p><span class="ti-email pr-3"></span>alumnosweb02@gmail.com</p>
                </div>
            </div>
        </div>
    </div>  -->


    <div class="bg-dark text-light text-center border-top wow fadeIn">
    <p class="mb-0 py-3 text-muted small">
        &copy; Copyright <script>document.write(new Date().getFullYear())</script> 
        Elaborado por JUAN PABLO CHÁVEZ LACAUD, JUAN CARLOS GONZÁLEZ GONZÁLEZ, CARLOS DAVID GONZÁLEZ SÁNCHEZ
    </p>
</div>
    <!--end of page footer -->

	<!-- core  -->
    <script src="assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="assets/vendors/bootstrap/bootstrap.bundle.js"></script>

    <!-- bootstrap affix -->
    <script src="assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <!-- wow.js -->
    <script src="assets/vendors/wow/wow.js"></script>
    
    <!-- google maps -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCtme10pzgKSPeJVJrG1O3tjR6lk98o4w8&callback=initMap"></script>

    <!-- FoodHut js -->
    <script src="assets/js/foodhut.js"></script>

</body>
</html>