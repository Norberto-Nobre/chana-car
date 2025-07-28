<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carola - Car Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        h1, h2, h3, h4, .font-poppins {
            font-family: 'Poppins', sans-serif;
        }
        .hero-bg {
            background-color: #f0f3f7;
            background-image: url('https://i.imgur.com/k4w2o3t.png'); /* Placeholder for a car image with transparent background */
            background-size: contain;
            background-repeat: no-repeat;
            background-position: 90% center;
        }
    </style>
</head>
<body class="bg-white text-gray-800">

    <!-- Top Bar -->
    <!-- <div class="bg-gray-900 text-gray-300 text-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-2">
            <div class="flex items-center space-x-4">
                <a href="#" class="flex items-center space-x-1 hover:text-white">
                    <span>EN</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </a>
                <a href="#" class="flex items-center space-x-1 hover:text-white">
                    <span>USD</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="hidden sm:flex items-center space-x-4">
                    <a href="#" class="hover:text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="hover:text-white"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="hover:text-white"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="hover:text-white"><i class="fab fa-youtube"></i></a>
                </div>
                <a href="#" class="font-medium hover:text-white">Sign In</a>
            </div>
        </div>
    </div> -->

    <!-- Main Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-4">
            <a href="#" class="text-3xl font-bold text-red-500 font-poppins">CAROLA</a>
            <div class="hidden lg:flex space-x-8 text-gray-900 font-semibold">
                <a href="#" class="text-red-500">Home</a>
                <a href="#" class="hover:text-red-500">About</a>
                <a href="#" class="hover:text-red-500">Service</a>
                <a href="#" class="hover:text-red-500">Blog</a>
                <a href="#" class="hover:text-red-500">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                 <a href="tel:+123456789" class="hidden sm:flex items-center space-x-2 text-gray-800">
                    <div class="bg-red-100 text-red-500 rounded-full h-10 w-10 flex items-center justify-center">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="text-sm">
                        <div class="font-bold">CALL US</div>
                        <div>+012 345 6789</div>
                    </div>
                </a>
                <button class="lg:hidden text-gray-800 text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main>
        <section class="relative bg-gray-100">
            <!-- Swiper -->
            <div class="swiper-container hero-swiper absolute inset-0 w-full h-full">
                <div class="swiper-wrapper">
                    <!-- Slide 1 -->
                    <div class="swiper-slide" style="background-image: url('https://car-rental-ten.vercel.app/assets/images/hero-slider/1.png'); background-color: #f0f3f7; background-size: contain; background-repeat: no-repeat; background-position: 90% center;"></div>
                    <!-- Slide 2 -->
                    <div class="swiper-slide" style="background-image: url('https://car-rental-ten.vercel.app/assets/images/hero-slider/2.png'); background-color: #f0f3f7; background-size: contain; background-repeat: no-repeat; background-position: 90% center;"></div>
                    <!-- Slide 3 -->
                    <div class="swiper-slide" style="background-image: url('https://car-rental-ten.vercel.app/assets/images/hero-slider/3.png'); background-color: #f0f3f7; background-size: contain; background-repeat: no-repeat; background-position: 90% center;"></div>
                </div>
                <!-- Add Navigation -->
                <div class="swiper-button-next text-red-500"></div>
                <div class="swiper-button-prev text-red-500"></div>
            </div>

            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 relative z-10">
                <div class="lg:w-1/2">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight font-poppins">
                        Find an affordable car for you
                    </h1>
                    <p class="mt-6 text-lg text-gray-600">
                        We are a company that provides car rental services with a wide selection of cars at affordable prices.
                    </p>
                    <div class="mt-8 flex items-center space-x-4">
                        <a href="#featured" class="bg-red-500 text-white px-8 py-3 rounded-md font-semibold hover:bg-red-600 transition-all duration-300">Our Cars</a>
                        <a href="#" class="flex items-center space-x-2 text-gray-800 font-semibold">
                            <span class="bg-white rounded-full h-12 w-12 flex items-center justify-center shadow-md"><i class="fas fa-play text-red-500"></i></span>
                            <span>Watch Video</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Booking Form Section -->
        <section class="bg-white relative -mt-16 z-10">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white p-8 rounded-lg shadow-2xl">
                    <form class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold">Pick-up Location</label>
                            <input type="text" placeholder="Search a location" class="w-full px-4 py-3 border border-gray-200 rounded-md focus:ring-red-500 focus:border-red-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold">Drop-off Location</label>
                            <input type="text" placeholder="Search a location" class="w-full px-4 py-3 border border-gray-200 rounded-md focus:ring-red-500 focus:border-red-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold">Pick-up Date</label>
                            <input type="date" class="w-full px-4 py-3 border border-gray-200 rounded-md focus:ring-red-500 focus:border-red-500 text-gray-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold">Drop-off Date</label>
                            <input type="date" class="w-full px-4 py-3 border border-gray-200 rounded-md focus:ring-red-500 focus:border-red-500 text-gray-500">
                        </div>
                        <button type="submit" class="w-full bg-red-500 text-white font-bold py-3 rounded-md hover:bg-red-600 transition text-lg">
                            Find Now
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Featured Vehicles -->
        <section id="featured" class="py-20 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-sm font-bold text-red-500 uppercase tracking-wider">Top Rated Cars</h2>
                    <p class="text-4xl font-bold text-gray-900 mt-2 font-poppins">Our Featured Cars</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Vehicle Card Example -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden group">
                        <div class="p-4">
                           <img src="https://i.imgur.com/QKl2kvx.png" alt="Car" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 font-poppins">Ferrari Enzo</h3>
                             <div class="flex items-baseline mt-1">
                                <span class="text-xl font-bold text-red-500">$350</span>
                                <span class="text-gray-500 ml-1">/ day</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-4 text-gray-600 text-sm">
                               <div class="flex items-center"><i class="fas fa-cogs mr-2 text-red-400"></i> Auto</div>
                               <div class="flex items-center"><i class="fas fa-gas-pump mr-2 text-red-400"></i> Petrol</div>
                               <div class="flex items-center"><i class="fas fa-road mr-2 text-red-400"></i> 2.5k</div>
                            </div>
                            <button class="mt-6 w-full bg-gray-900 text-white font-bold py-3 rounded-md hover:bg-red-500 transition">Rent Now</button>
                        </div>
                    </div>
                    <!-- Add more cards as needed -->
                     <div class="bg-white rounded-lg shadow-md overflow-hidden group">
                        <div class="p-4">
                           <img src="https://i.imgur.com/k6K3c3S.png" alt="Car" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 font-poppins">Ferrari F8</h3>
                             <div class="flex items-baseline mt-1">
                                <span class="text-xl font-bold text-red-500">$400</span>
                                <span class="text-gray-500 ml-1">/ day</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-4 text-gray-600 text-sm">
                               <div class="flex items-center"><i class="fas fa-cogs mr-2 text-red-400"></i> Auto</div>
                               <div class="flex items-center"><i class="fas fa-gas-pump mr-2 text-red-400"></i> Petrol</div>
                               <div class="flex items-center"><i class="fas fa-road mr-2 text-red-400"></i> 3.1k</div>
                            </div>
                            <button class="mt-6 w-full bg-gray-900 text-white font-bold py-3 rounded-md hover:bg-red-500 transition">Rent Now</button>
                        </div>
                    </div>
                     <div class="bg-white rounded-lg shadow-md overflow-hidden group">
                        <div class="p-4">
                           <img src="https://i.imgur.com/w1pNe2s.png" alt="Car" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 font-poppins">Lamborghini Huracan</h3>
                             <div class="flex items-baseline mt-1">
                                <span class="text-xl font-bold text-red-500">$450</span>
                                <span class="text-gray-500 ml-1">/ day</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-4 text-gray-600 text-sm">
                               <div class="flex items-center"><i class="fas fa-cogs mr-2 text-red-400"></i> Auto</div>
                               <div class="flex items-center"><i class="fas fa-gas-pump mr-2 text-red-400"></i> Petrol</div>
                               <div class="flex items-center"><i class="fas fa-road mr-2 text-red-400"></i> 1.8k</div>
                            </div>
                            <button class="mt-6 w-full bg-gray-900 text-white font-bold py-3 rounded-md hover:bg-red-500 transition">Rent Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-2xl font-bold text-red-500 font-poppins">CAROLA</h3>
                        <p class="mt-4">We are a company that provides car rental services with a wide selection of cars at affordable prices.</p>
                         <div class="flex mt-4 space-x-4">
                            <a href="#" class="hover:text-white"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="hover:text-white"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="hover:text-white"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="hover:text-white"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-white font-poppins">Company</h4>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="hover:text-white">About Us</a></li>
                            <li><a href="#" class="hover:text-white">Our Service</a></li>
                            <li><a href="#" class="hover:text-white">Blog</a></li>
                            <li><a href="#" class="hover:text-white">Contact Us</a></li>
                        </ul>
                    </div>
                    <div>
                         <h4 class="text-lg font-semibold text-white font-poppins">Contact Info</h4>
                         <ul class="mt-4 space-y-3">
                             <li class="flex items-start"><i class="fas fa-map-marker-alt mt-1 mr-3 text-red-500"></i> 25/B Milford Road, New York</li>
                             <li class="flex items-start"><i class="fas fa-phone-alt mt-1 mr-3 text-red-500"></i> +012 345 6789</li>
                             <li class="flex items-start"><i class="fas fa-envelope mt-1 mr-3 text-red-500"></i> info@carola.com</li>
                         </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-white font-poppins">Newsletter</h4>
                        <p class="mt-4">Subscribe to our newsletter for the latest updates and offers.</p>
                        <form class="mt-4 flex">
                            <input type="email" placeholder="Email Address" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-l-md text-white focus:outline-none focus:border-red-500">
                            <button class="bg-red-500 text-white px-4 rounded-r-md hover:bg-red-600 transition">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="bg-black py-4">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500">
                    <p>&copy; 2024 Carola. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </main>

</body>
</html>