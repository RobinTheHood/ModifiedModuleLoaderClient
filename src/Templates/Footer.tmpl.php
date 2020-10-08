<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<?php use RobinTheHood\ModifiedModuleLoaderClient\App; ?>

<script src="<?php echo App::getUrlRoot();?>/src/Templates/Scripts/bootstrap.bundle.min.js"></script>
<script src="<?php echo App::getUrlRoot();?>/src/Templates/Scripts/lightbox.js"></script>

<script>
    function filterModule(searchString)
    {
        var moduleSearchBoxes = $('.module-serach-box');
        moduleSearchBoxes.each(function() {
            var tags = $(this).data('tags');

            if (!searchString) {
                $(this).show();
                return;
            }

            if (tags.toLowerCase().includes(searchString.toLowerCase())) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Überschriften und Kategorien ausblenden
        $('.modules h2').each(function() {
            $(this).show();
        });

        $('.modules .category').each(function() {
            $(this).show();
        });

        $('.modules .category').each(function(index) {
            var a = $('.module-serach-box:visible', this);

            if (a.length != 0) {
                return;
            }

            $('.modules h2').each(function(index2) {
                if (index2 == index) {
                    $(this).hide();
                }
            })

            $('.modules .category').each(function(index2) {
                if (index2 == index) {
                    $(this).hide();
                }
            })
        });
    }

    $(".alert-success.auto-fade-out").fadeTo(2000, 0).slideUp(500, function() {
        $(this).remove();
    });

    function copyToClipboard(elementId)
    {
        document.getElementById(elementId).select();
        document.execCommand('copy');
    }
</script>
