<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    {{ get_title() }}

    <link href="{{ static_url('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ static_url('css/admin.css') }}" rel="stylesheet" type="text/css">

    {{ clip('style') }}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
  </head>
  <body>
    <div id="wrapper">
      <!-- Navigation -->
      <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">CRUD Generator</a>
        </div>
        <!-- /.navbar-header -->

        <ul class="nav navbar-top-links navbar-right">
          <!-- /.dropdown -->
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-user">
              <li><a href="#"><i class="fa fa-user fa-fw"></i> admin</a></li>
              <li class="divider"></li>
              <li><a href="#"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
              </li>
            </ul>
            <!-- /.dropdown-user -->
          </li>
          <!-- /.dropdown -->
        </ul>
        <!-- /.navbar-top-links -->

        <div class="navbar-default sidebar" role="navigation">
          <div class="sidebar-nav navbar-collapse">
            <ul class="nav" id="side-menu">
              {% for menu in menus() %}
              <li>
                <a href="{{ url(menu.url) }}">
                  <i class="fa fa-th fa-fw"></i> {{ menu.label }}
                </a>
              </li>
              {% endfor %}
            </ul>
          </div>
        </div>
      </nav>

      <div id="page-wrapper">
        {{ content() }}
      </div>
      <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <script src="{{ static_url('js/require.js') }}"></script>
    <script>
requirejs.config({
    baseUrl: "{{ static_url('js/') }}",
    shim : {
        "bootstrap"  : {"deps": ["jquery"]},
        "metisMenu"  : {"deps": ["jquery"]},
        "sb-admin-2" : {"deps": ["metisMenu", "jquery"]},
        "datatables" : {"deps": ["jquery"]},
        "bootstrap.datetimepicker": {"deps": ["bootstrap", "moment"]},
        "bootstrap.wysihtml5": {"deps": ["bootstrap"]}
    },
    paths: {
        "text"       : "require/text",
        "datatables" : "jquery.dataTables",
    }
});
requirejs(["bootstrap", "sb-admin-2"]);
    </script>
    {{ clip('script') }}
  </body>
</html>
