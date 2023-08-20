# Advanced JavaScript Bundling

- Delay rendering of non-important components on cacheable pages
- Use bundles on non-cacheable pages.

# Installation

    composer require pure_mashiro/magento2-bundlejs

# PageSpeed scores with Luma theme

![Mobile](https://marketplace.magento.com/media/catalog/product/f/e/fec4_image4.png?store=default&image-type=image&fit=bounds)
![Desktop](https://marketplace.magento.com/media/catalog/product/3/a/3a7a_image10.png?store=default&image-type=image&fit=bounds)

# External links

[Demo Video](https://youtu.be/nTiZuwHTS-E)

[Magento Marketplace](https://marketplace.magento.com/pure-mashiro-magento2-bundlejs.html)

[User Guide](https://marketplace.magento.com/media/catalog/product/pure_mashiro-magento2-bundlejs-1-3-1-ce/user_guides.pdf)

# My perspective

Magento 2 uses require JS to load JS when they are necessary. However, when the page just starts, it already needs to load almost 200 JS (or even more) files.
In HTTP/1, it is a fatal weakness because the file needs to be downloaded one by one which greatly increase the loading time.
However, with HTTP/2 enabled, this weakness of the Magento 2 front is covered and no longer a problem.
Therefore, now, the core web vital of the Magento 2 luma theme is pretty decent.
Except for the product detail page, it has a poor CLS. There is a git issue for it, which I hope will be merged into the develop branch in the future: https://github.com/magento/magento2/pull/33265. The dev can always migrate the fix ahead as well.

Customized Magento 2 website still usually has a low google speed score compared to the Luma default. The score starts becoming low when we start using 3rd party JS and customize without taking CLS into account (Slider and other bad JS codes usually degrade CLS).
The page tends to load JS first even though the image tag is before the JS which leads to the LCP vital will get degraded when using 3rd party JS.

Hyva theme will also have the same problem. The page speed score of the Hyva theme will become lower if the page uses 3rd party JS. The score in mobile can be reduced from 9x to 70 or 50 or even 30 depending on the number of 3rd party JS.
Don't get me wrong. I don't say 70 or 50 is a bad result, it is good but it won't remain 9x score. They mention "Out of the box, Hyvä scores a 100/100 score in Google PageSpeed and it passes Core Web Vitals on all metrics". However, the Luma theme by default can also have a decent score 8x-9x/9x in Google PageSpeed (except for the product detail page because of the CLS issue).

Advanced bundle JS (not the native Magento bundle) is usually a solution for HTTP/1. However, with HTTP/2, I see that it is not necessary.
With Luma default, with advanced bundle JS, the score will be even lower compared to when not using bundle. The problem is similar to with 3rd party JS. The page tends to load JS first but because the JS is now bundled, the file size got bigger and degraded LCP.

That is why, in my extension, it is recommended to use bundle only on non-cacheable pages.
To further improve loading time, the extension has a feature to delay JS execution in which non-important components will be only rendered when the user starts interacting with the page.
