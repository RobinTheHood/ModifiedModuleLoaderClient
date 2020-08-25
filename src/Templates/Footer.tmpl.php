<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<script src="src/Templates/Scripts/bootstrap.bundle.min.js"></script>
<script src="src/Templates/Scripts/lightbox.js"></script>

<script>
    function filterModule()
    {
        var filterModuleSearch = $('#filterModuleSearch');
        var filterModuleOptionFree = $('#filterModuleOptionFree');

        var moduleSearchBoxes = $('.module-serach-box');
        var searchString = filterModuleSearch.val();

        var considerTags = searchString ? true : false;
        var considerPrice = $('#filterModuleOptionFree').is(":checked");

        moduleSearchBoxes.each(function() {
            var tags = $(this).data('tags');
            var price = $(this).data('price');

            var tagsMatch = false;
            var priceMatch = false;

            /**
             * Filter by Tags
             */
            if (tags.toLowerCase().includes(searchString.toLowerCase()) || !considerTags) {
                tagsMatch = true;
            }

            /**
             * Filter by Price
             */
            if (price === 'free' || !considerPrice) {
                priceMatch = true;
            }

            if (tagsMatch && priceMatch) {
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
</script>
