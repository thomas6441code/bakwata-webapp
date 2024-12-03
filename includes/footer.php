    <!-- Footer -->
    <?php
    $currentYear = date("Y");
    ?>

    <footer class="bg-gray-900 text-white px-4 lg:px-5 md:px-5 pt-20">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Logo Section -->
                <div class="flex items-center justify-center">
                    <img
                        src="./public/images/bakwata.png"
                        alt="Bakwata Logo"
                        class="h-32 w-32 object-contain"
                        width="700"
                        height="700" />
                </div>

                <!-- About BAKWATA Section -->
                <div class="space-y-4">
                    <h4 class="text-[1rem] font-semibold text-gray-100">ABOUT BAKWATA</h4>
                    <p class="text-gray-400 text-sm md:pr-10">
                        The National Muslim Council of Tanzania (BAKWATA) is a well-established faith-based
                        Islamic organization registered since 1968 and a premier Muslim umbrella organization
                        recognized in Tanzania and International.
                    </p>
                </div>

                <!-- Quick Links Section -->
                <div class="space-y-2 lg:pl-10">
                    <h4 class="text-[1rem] font-semibold text-gray-100">QUICK LINKS</h4>
                    <ul class="text-gray-400 text-sm space-y-2">
                        <li><a href="https://bakwatahajjumrah.or.tz" target="_blank" class="hover:text-white">Hajj & Umrah</a></li>
                        <li><a href="https://bakaid.or.tz" target="_blank" class="hover:text-white">Bakaid</a></li>
                        <li><a href="projects.php" class="hover:text-white">Projects</a></li>
                        <li><a href="services.php" class="hover:text-white">Services</a></li>
                        <li><a href="events.php" class="hover:text-white">News Updates</a></li>
                    </ul>
                </div>

                <!-- Contact Us Section -->
                <div class="space-y-3">
                    <h4><a href="events.php" class="text-[1rem] font-semibold text-gray-100">CONTACT US</a></h4>
                    <p class="text-gray-400 text-sm flex items-center justify-start">
                        <span class="text-white mr-2"><i class="fas fa-map-marker-alt"></i></span>
                        P.O. Box 21422, Dar Es Salaam
                    </p>
                    <p class="text-gray-400 text-sm flex items-center justify-start">
                        <span class="text-white mr-2"><i class="fas fa-phone"></i></span>
                        <a href="tel:+255123456789">+255 754 453200</a>

                    </p>
                    <p class="text-gray-400 text-sm flex items-center justify-start">
                        <span class="text-white mr-2"><i class="fas fa-phone"></i></span>
                        <a href="tel:+255123456789">+255 717 011 207</a>

                    </p>
                    <p class="text-gray-400 text-sm flex items-center justify-start">
                        <span class="text-white mr-2"><i class="fas fa-envelope"></i></span>
                        <a href="mailto:info@bakwata.or.tz">info@bakwata.or.tz</a>
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-700 py-4 mt-6 text-center">
                <p class="text-gray-500 text-sm">
                    Copyright © BAKWATA <?= $currentYear ?>. All Rights Reserved
                </p>
            </div>
        </div>
    </footer>


    </body>

    </html>