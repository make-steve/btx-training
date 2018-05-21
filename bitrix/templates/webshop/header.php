<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Bootstrap E-Commerce Template- DIGI Shop mini</title>

    <!-- Bootstrap core CSS -->
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/assets/css/bootstrap.css")?>" type="text/css" rel="stylesheet" />
    <!-- <link href="assets/css/bootstrap.css" rel="stylesheet"> -->
    <!-- Fontawesome core CSS -->
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/assets/css/font-awesome.min.css")?>" type="text/css" rel="stylesheet" />
    <!-- <link href="assets/css/font-awesome.min.css" rel="stylesheet" /> -->
    <!--GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <!--Slide Show Css -->
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/assets/ItemSlider/css/main-style.css")?>" type="text/css" rel="stylesheet" />
    <!-- <link href="assets/ItemSlider/css/main-style.css" rel="stylesheet" /> -->
    <!-- custom CSS here -->
    <link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/assets/css/style.css")?>" type="text/css" rel="stylesheet" />
    <!-- <link href="assets/css/style.css" rel="stylesheet" /> -->
    <?//CJSCore::Init();?>
<?$APPLICATION->ShowCSS(true, true);?>
<?//$APPLICATION->ShowHeadStrings();?>
<?//$APPLICATION->ShowHeadScripts();?>
</head>
<body class="<?=$APPLICATION->ShowProperty("BodyClass");?>">
	    <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><strong>DIGI</strong> Shop</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">


                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#">Track Order</a></li>
                    <li><a href="#">Login</a></li>
                    <li><a href="#">Signup</a></li>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">24x7 Support <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><strong>Call: </strong>+09-456-567-890</a></li>
                            <li><a href="#"><strong>Mail: </strong>info@yourdomain.com</a></li>
                            <li class="divider"></li>
                            <li><a href="#"><strong>Address: </strong>
                                <div>
                                    234, New york Street,<br />
                                    Just Location, USA
                                </div>
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <form class="navbar-form navbar-right" role="search">
                    <div class="form-group">
                        <input type="text" placeholder="Enter Keyword Here ..." class="form-control">
                    </div>
                    &nbsp; 
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>