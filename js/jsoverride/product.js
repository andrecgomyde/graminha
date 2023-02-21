Product.OptionsPrice.prototype.reload = Product.OptionsPrice.prototype.reload.wrap(function(parentMethod){
    var price;
    var formattedPrice;
    var optionPrices = this.getOptionPrices();
    var nonTaxable = optionPrices[1];
    var optionOldPrice = optionPrices[2];
    var priceInclTax = optionPrices[3];
    optionPrices = optionPrices[0];

    $H(this.containers).each(function(pair) {
        var _productPrice;
        var _plusDisposition;
        var _minusDisposition;
        var _priceInclTax;
        if ($(pair.value)) {
            if (pair.value == 'old-price-'+this.productId && this.productOldPrice != this.productPrice) {
                _productPrice = this.productOldPrice;
                _plusDisposition = this.oldPlusDisposition;
                _minusDisposition = this.oldMinusDisposition;
            } else {
                _productPrice = this.productPrice;
                _plusDisposition = this.plusDisposition;
                _minusDisposition = this.minusDisposition;
            }
            _priceInclTax = priceInclTax;

            if (pair.value == 'old-price-'+this.productId && optionOldPrice !== undefined) {
                price = optionOldPrice+parseFloat(_productPrice);
            } else if (this.specialTaxPrice == 'true' && this.priceInclTax !== undefined && this.priceExclTax !== undefined) {
                price = optionPrices+parseFloat(this.priceExclTax);
                _priceInclTax += this.priceInclTax;
            } else {
                price = optionPrices+parseFloat(_productPrice);
                _priceInclTax += parseFloat(_productPrice) * (100 + this.currentTax) / 100;
            }

            if (this.specialTaxPrice == 'true') {
                var excl = price;
                var incl = _priceInclTax;
            } else if (this.includeTax == 'true') {
                // tax = tax included into product price by admin
                var tax = price / (100 + this.defaultTax) * this.defaultTax;
                var excl = price - tax;
                var incl = excl*(1+(this.currentTax/100));
            } else {
                var tax = price * (this.currentTax / 100);
                var excl = price;
                var incl = excl + tax;
            }

            var subPrice = 0;
            var subPriceincludeTax = 0;
            Object.values(this.customPrices).each(function(el){
                if (el.excludeTax && el.includeTax) {
                    subPrice += parseFloat(el.excludeTax);
                    subPriceincludeTax += parseFloat(el.includeTax);
                } else {
                    subPrice += parseFloat(el.price);
                    subPriceincludeTax += parseFloat(el.price);
                }
            });
            excl += subPrice;
            incl += subPriceincludeTax;

            if (typeof this.exclDisposition == 'undefined') {
                excl += parseFloat(_plusDisposition);
            }

            incl += parseFloat(_plusDisposition) + parseFloat(this.plusDispositionTax);
            excl -= parseFloat(_minusDisposition);
            incl -= parseFloat(_minusDisposition);

            //adding nontaxlable part of options
            excl += parseFloat(nonTaxable);
            incl += parseFloat(nonTaxable);

            if (pair.value == 'price-including-tax-'+this.productId) {
                price = incl;
            } else if (pair.value == 'price-excluding-tax-'+this.productId) {
                price = excl;
            } else if (pair.value == 'old-price-'+this.productId) {
                if (this.showIncludeTax || this.showBothPrices) {
                    price = incl;
                } else {
                    price = excl;
                }
            } else {
                if (this.showIncludeTax) {
                    price = incl;
                } else {
                    price = excl;
                }
            }

            if (price < 0) price = 0;

            if (price > 0 || this.displayZeroPrice) {
                formattedPrice = this.formatPrice(price);
            } else {
                formattedPrice = '';
            }

            if ($(pair.value).select('.price')[0]) {
                $(pair.value).select('.price')[0].innerHTML = formattedPrice;
                if ($(pair.value+this.duplicateIdSuffix) && $(pair.value+this.duplicateIdSuffix).select('.price')[0]) {
                    $(pair.value+this.duplicateIdSuffix).select('.price')[0].innerHTML = formattedPrice;
                }
            } else {
                $(pair.value).innerHTML = formattedPrice;
                if ($(pair.value+this.duplicateIdSuffix)) {
                    $(pair.value+this.duplicateIdSuffix).innerHTML = formattedPrice;
                }
            }
            $('custom_option').setValue(subPrice);

        };
    }.bind(this));

    if (typeof(skipTierPricePercentUpdate) === "undefined" && typeof(this.tierPrices) !== "undefined") {
        for (var i = 0; i < this.tierPrices.length; i++) {
            $$('.benefit').each(function(el) {
                var parsePrice = function(html) {
                    var format = this.priceFormat;
                    var decimalSymbol = format.decimalSymbol === undefined ? "," : format.decimalSymbol;
                    var regexStr = '[^0-9-' + decimalSymbol + ']';
                    //remove all characters except number and decimal symbol
                    html = html.replace(new RegExp(regexStr, 'g'), '');
                    html = html.replace(decimalSymbol, '.');
                    return parseFloat(html);
                }.bind(this);

                var updateTierPriceInfo = function(priceEl, tierPriceDiff, tierPriceEl, benefitEl) {
                    if (typeof(tierPriceEl) === "undefined") {
                        //tierPrice is not shown, e.g., MAP, no need to update the tier price info
                        return;
                    }
                    var price = parsePrice(priceEl.innerHTML);
                    var tierPrice = price + tierPriceDiff;

                    tierPriceEl.innerHTML = this.formatPrice(tierPrice);

                    var $percent = Selector.findChildElements(benefitEl, ['.percent.tier-' + i]);
                    $percent.each(function(el) {
                        el.innerHTML = Math.ceil(100 - ((100 / price) * tierPrice));
                    });
                }.bind(this);

                var tierPriceElArray = $$('.tier-price.tier-' + i + ' .price');
                if (this.showBothPrices) {
                    var containerExclTax = $(this.containers[3]);
                    var tierPriceExclTaxDiff = this.tierPrices[i];
                    var tierPriceExclTaxEl = tierPriceElArray[0];
                    updateTierPriceInfo(containerExclTax, tierPriceExclTaxDiff, tierPriceExclTaxEl, el);
                    var containerInclTax = $(this.containers[2]);
                    var tierPriceInclTaxDiff = this.tierPricesInclTax[i];
                    var tierPriceInclTaxEl = tierPriceElArray[1];
                    updateTierPriceInfo(containerInclTax, tierPriceInclTaxDiff, tierPriceInclTaxEl, el);
                } else if (this.showIncludeTax) {
                    var container = $(this.containers[0]);
                    var tierPriceInclTaxDiff = this.tierPricesInclTax[i];
                    var tierPriceInclTaxEl = tierPriceElArray[0];
                    updateTierPriceInfo(container, tierPriceInclTaxDiff, tierPriceInclTaxEl, el);
                } else {
                    var container = $(this.containers[0]);
                    var tierPriceExclTaxDiff = this.tierPrices[i];
                    var tierPriceExclTaxEl = tierPriceElArray[0];
                    updateTierPriceInfo(container, tierPriceExclTaxDiff, tierPriceExclTaxEl, el);
                }
            }, this);
        }
    }
    jQuery('#bss_configurablegridview .qty_att_product').change();
});