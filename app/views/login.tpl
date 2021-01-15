{include file="header.tpl"}
<!-- Wrapper -->
<div id="wrapper">

    <!-- Main -->
    <div id="main">
        <div class="inner">

            <!-- Header -->
            {include file="pageHeader.tpl"}

            <!-- Banner -->
            <section id="banner">
                <div class="content">
                    <header>
                        <div class="align-center">
                            <h2>Zaloguj się</h2>
                        </div>
                    </header>
                    <form method="POST" action="http://192.168.0.108/working_hours_app/public/?action=login">
                        <div class="row gtr-uniform">
                            <div class="col-4 col-2-large"></div>
                            <div class="col-4 col-12-small col-8-large">
                                <input type="text" name="email" id="email" placeholder="Email">
                            </div>
                            <div class="col-4 col-2-large"></div>
                            <div class="col-4 col-2-large"></div>
                            <div class="col-4 col-12-small col-8-large">
                                <input type="password" name="password" id="password" placeholder="Hasło">
                            </div>
                            <div class="col-4 col-2-large"></div>

                            <div class="col-4 col-2-large"></div>
                            <div class="col-4 col-12-small col-8-large">
                                <input type="checkbox" id="remember-me" name="remember-me">
                                <label for="demo-copy">Zapamiętaj mnie</label>
                            </div>
                            <div class="col-4 col-2-large"></div>
                            <div class="col-4 col-2-large"></div>
                            <div class="col-4 col-12-small col-8-large">
                                <input type="submit" value="Zaloguj" class="primary fit">
                            </div>
                            <div class="col-12">
                                {include file="messages.tpl"}
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>
{include file="footer.tpl"}
