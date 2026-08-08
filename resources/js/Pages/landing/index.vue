<script>
import { Link, Head } from '@inertiajs/vue3';

export default {
    components: { Link, Head },
    data() {
        return {
            showBackToTop: false,
            isScrolled: false,
            services: [
                { icon: '🍛', name: 'Food Services', desc: 'Homemade food, lunch boxes, catering, bakery, snacks', status: 'coming-soon' },
                { icon: '🚖', name: 'Local Transport', desc: 'Auto, taxi, van, parcel delivery', status: 'coming-soon' },
                { icon: '🏠', name: 'Home Services', desc: 'Electrician, plumber, carpenter, AC, CCTV, RO', status: 'coming-soon' },
                { icon: '🧹', name: 'Cleaning', desc: 'Home, office, deep cleaning, tank cleaning', status: 'coming-soon' },
                { icon: '🌾', name: 'Agriculture', desc: 'Tractor rental, farm equipment, labour, harvest', status: 'coming-soon' },
                { icon: '🛒', name: 'Marketplace', desc: 'Buy & sell, second-hand, rentals, local shops', status: 'coming-soon' },
                { icon: '🎉', name: 'Events', desc: 'Decoration, sound, photography, stage setup', status: 'coming-soon' },
                { icon: '🚑', name: 'Emergency', desc: 'Ambulance, blood donors, volunteers, hospitals', status: 'coming-soon' },
                { icon: '📦', name: 'Delivery', desc: 'Local, grocery, medicine, courier assistance', status: 'coming-soon' },
                { icon: '🎓', name: 'Education', desc: 'Tuition, skill training, coaching, online classes', status: 'coming-soon' },
                { icon: '💼', name: 'Professional', desc: 'Accountant, lawyer, tax consultant, insurance', status: 'coming-soon' },
            ],
        };
    },
    mounted() {
        window.addEventListener('scroll', this.handleScroll);
    },
    beforeUnmount() {
        window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
        handleScroll() {
            this.showBackToTop = window.scrollY > 400;
            this.isScrolled = window.scrollY > 50;
        },
        scrollToSection(sectionId) {
            const el = document.getElementById(sectionId);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    },
};
</script>

<template>
    <Head title="orezone - Community Sharing Platform" />
    <a href="#home" class="visually-hidden-focusable position-absolute top-0 start-0 bg-primary text-white px-3 py-2" style="z-index: 1050;">Skip to main content</a>

    <!-- ==================== NAVBAR ==================== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm" :class="{ 'shadow': isScrolled }">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-white" href="#home">orezone</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#orezoneNav" aria-controls="orezoneNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="orezoneNav">
                <ul class="navbar-nav ms-auto me-3 gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="#services" @click.prevent="scrollToSection('services')">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works" @click.prevent="scrollToSection('how-it-works')">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#trust-safety" @click.prevent="scrollToSection('trust-safety')">Trust &amp; Safety</a>
                    </li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    <Link href="/login" class="btn btn-outline-light btn-sm">Login</Link>
                    <Link href="/login" class="btn btn-light btn-sm text-primary fw-semibold">Get Started</Link>
                    <Link href="/login" class="btn btn-light btn-sm text-primary fw-semibold">Offer a Service</Link>
                </div>
            </div>
        </div>
    </nav>

    <main id="main-content">

    <!-- ==================== HERO ==================== -->
    <section id="home" class="bg-primary text-white position-relative overflow-hidden">
        <div class="container py-5 position-relative" style="z-index: 2;">
            <div class="row align-items-center py-5" style="min-height: 50vh">
                <div class="col-lg-7 mb-5 mb-lg-0">
                    <div class="badge bg-white text-primary fw-semibold mb-3 px-3 py-2">🚀 Ride Sharing — Now Live!</div>
                    <h1 class="display-3 fw-bold mb-4 lh-1">
                        Your Community.<br>Your Services.<br>Connected.
                    </h1>
                    <p class="lead mb-4 opacity-90">
                        orezone is where neighbors share rides, food, skills, and services.
                        One platform to connect your community — saving costs, building trust,
                        and supporting local.
                    </p>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <Link href="/coming-soon" class="btn btn-light btn-lg text-primary fw-semibold px-4">
                            🔍 Find a Service
                        </Link>
                        <Link href="/coming-soon" class="btn btn-outline-light btn-lg px-4">
                            🚗 Share a Ride
                        </Link>
                    </div>
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">🏘️</span>
                            <span class="small opacity-75">100% Community</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">🔒</span>
                            <span class="small opacity-75">Verified Members</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">💚</span>
                            <span class="small opacity-75">Support Local</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="bg-white bg-opacity-10 rounded-4 p-5">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <div class="fs-1">🚗</div>
                                    <small class="d-block mt-1 opacity-75">Rides</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <div class="fs-1">🍛</div>
                                    <small class="d-block mt-1 opacity-75">Food</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <div class="fs-1">🏠</div>
                                    <small class="d-block mt-1 opacity-75">Homes</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <div class="fs-1">🎓</div>
                                    <small class="d-block mt-1 opacity-75">Learn</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <div class="fs-1">🛒</div>
                                    <small class="d-block mt-1 opacity-75">Market</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <div class="fs-1">🚑</div>
                                    <small class="d-block mt-1 opacity-75">Help</small>
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 small opacity-75">And many more services coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Decorative wave -->
        <div class="position-absolute bottom-0 start-0 w-100" style="line-height: 0;">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%;">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f8f9fa"/>
            </svg>
        </div>
    </section>

    <!-- ==================== LAUNCHED: RIDE SHARING ==================== -->
    <section id="ride-sharing" class="py-5 bg-light">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="badge bg-success text-white fw-semibold mb-3 px-3 py-2">✅ Now Live</div>
                    <h2 class="display-5 fw-bold text-dark mb-4">Community Ride Sharing</h2>
                    <p class="text-muted fs-5 mb-4">
                        Share your commute, save on fuel, and build real connections. Whether you're a host
                        offering seats or a traveler looking for an affordable ride — orezone makes it safe,
                        simple, and transparent.
                    </p>
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="fs-2">🚗</span>
                                <div>
                                    <h6 class="fw-bold mb-1">Offer a Trip</h6>
                                    <p class="text-muted small mb-0">Post your route, set seats, and share costs with fellow commuters.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="fs-2">🔍</span>
                                <div>
                                    <h6 class="fw-bold mb-1">Find a Ride</h6>
                                    <p class="text-muted small mb-0">Search trips by route, time, and seats. Book instantly or request approval.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="fs-2">💰</span>
                                <div>
                                    <h6 class="fw-bold mb-1">Share Costs</h6>
                                    <p class="text-muted small mb-0">Fair, transparent pricing. No surge, no hidden fees.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="fs-2">🛡️</span>
                                <div>
                                    <h6 class="fw-bold mb-1">Verified & Safe</h6>
                                    <p class="text-muted small mb-0">Document-verified hosts, ratings, SOS alerts, and emergency contacts.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <Link href="/coming-soon" class="btn btn-primary text-white px-4">Find a Trip</Link>
                        <Link href="/coming-soon" class="btn btn-outline-primary px-4">Offer a Trip</Link>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="bg-white rounded-4 p-5 shadow-sm">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <div class="display-6 fw-bold text-primary">3</div>
                                    <small class="text-muted">Simple Steps</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <div class="display-6 fw-bold text-success">0</div>
                                    <small class="text-muted">Hidden Fees</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <div class="display-6 fw-bold text-info">24/7</div>
                                    <small class="text-muted">Safety Support</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-start">
                            <h6 class="fw-bold text-dark">How it works:</h6>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span class="badge bg-primary rounded-circle p-2">1</span>
                                <span class="text-muted small">Travelers search and book available seats</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span class="badge bg-primary rounded-circle p-2">2</span>
                                <span class="text-muted small">Hosts accept or approve bookings</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary rounded-circle p-2">3</span>
                                <span class="text-muted small">Share the ride, split the cost, build community</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== HOW IT WORKS (General) ==================== -->
    <section id="how-it-works" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark">How orezone Works</h2>
                <p class="text-muted fs-5">One platform, many services — all community-powered</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="display-5">📱</span>
                        </div>
                        <h3 class="h5 fw-bold">Sign Up</h3>
                        <p class="text-muted small">Create your account with phone verification. One account for all services.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="display-5">🔍</span>
                        </div>
                        <h3 class="h5 fw-bold">Discover</h3>
                        <p class="text-muted small">Browse rides, food, services, and more from verified community members.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-4">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="display-5">🤝</span>
                        </div>
                        <h3 class="h5 fw-bold">Connect</h3>
                        <p class="text-muted small">Book, request, or hire directly. Transparent pricing, no middlemen.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="display-5">⭐</span>
                        </div>
                        <h3 class="h5 fw-bold">Rate & Trust</h3>
                        <p class="text-muted small">Two-way ratings build trust. Verified profiles keep everyone safe.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FUTURE SERVICES MARKETPLACE ==================== -->
    <section id="services" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark">Community Service Marketplace</h2>
                <p class="text-muted fs-5">Ride sharing is just the beginning. Here's what's coming next.</p>
            </div>

            <!-- Live service badge -->
            <div class="text-center mb-4">
                <span class="badge bg-success text-white px-3 py-2 fs-6">🚗 Ride Sharing — LIVE NOW</span>
            </div>

            <div class="row g-3">
                <div v-for="service in services" :key="service.name" class="col-6 col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 position-relative">
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary small">Coming Soon</span>
                        </div>
                        <div class="display-5 mb-3">{{ service.icon }}</div>
                        <h5 class="h6 fw-bold mb-1">{{ service.name }}</h5>
                        <p class="text-muted small mb-0">{{ service.desc }}</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <div class="bg-white rounded-4 p-4 shadow-sm d-inline-block">
                    <p class="text-muted mb-3 fw-semibold">
                        Want to be notified when we launch a new service?
                    </p>
                    <Link href="/coming-soon" class="btn btn-primary text-white px-4">
                        Get Notified →
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== WHY OREZONE ==================== -->
    <section id="why-orezone" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark">Why orezone?</h2>
                <p class="text-muted fs-5">Built for communities, not corporations</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <div class="display-5 mb-3">🏘️</div>
                        <h5 class="fw-bold">Community First</h5>
                        <p class="text-muted">Every service connects you with verified neighbors and local providers. Real people, real trust.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <div class="display-5 mb-3">💲</div>
                        <h5 class="fw-bold">Fair Pricing</h5>
                        <p class="text-muted">No surge pricing, no hidden fees. Small platform fee keeps the service running, nothing more.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <div class="display-5 mb-3">🔒</div>
                        <h5 class="fw-bold">Verified & Safe</h5>
                        <p class="text-muted">Document verification, two-way ratings, SOS alerts, and emergency contacts for every service.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <div class="display-5 mb-3">💚</div>
                        <h5 class="fw-bold">Support Local</h5>
                        <p class="text-muted">Every booking supports a local provider. Keep money in your community, not in corporate pockets.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <div class="display-5 mb-3">📱</div>
                        <h5 class="fw-bold">One Platform</h5>
                        <p class="text-muted">One account for rides, food, home services, marketplace, and more. No need to switch apps.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4">
                        <div class="display-5 mb-3">🌱</div>
                        <h5 class="fw-bold">Grow Together</h5>
                        <p class="text-muted">As the community grows, more services launch. Everyone benefits from a stronger local network.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== TRUST & SAFETY ==================== -->
    <section id="trust-safety" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark">Safety First, Always</h2>
                <p class="text-muted fs-5">Every feature is designed with your safety and peace of mind</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="display-5 mb-3">🔐</div>
                        <h5 class="fw-bold">Verified Members</h5>
                        <p class="text-muted">Every provider undergoes identity and document verification before offering services.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="display-5 mb-3">📋</div>
                        <h5 class="fw-bold">Document Review</h5>
                        <p class="text-muted">Licenses, vehicle documents, and credentials are reviewed for compliance and safety.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="display-5 mb-3">🚨</div>
                        <h5 class="fw-bold">SOS Alerts</h5>
                        <p class="text-muted">One-tap SOS connects you with emergency services and notifies your trusted contacts.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="display-5 mb-3">⭐</div>
                        <h5 class="fw-bold">Two-Way Ratings</h5>
                        <p class="text-muted">Mutual ratings ensure accountability and help maintain a trustworthy community.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="display-5 mb-3">💲</div>
                        <h5 class="fw-bold">Transparent Fees</h5>
                        <p class="text-muted">No hidden costs. See the full breakdown before you confirm any service or booking.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="display-5 mb-3">📞</div>
                        <h5 class="fw-bold">Emergency Contacts</h5>
                        <p class="text-muted">Share activity details with family and friends so they know where you are.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FINAL CTA ==================== -->
    <section class="py-5 bg-primary text-white position-relative overflow-hidden">
        <div class="container py-5 text-center position-relative" style="z-index: 2;">
            <h2 class="display-5 fw-bold mb-4">Join the Community</h2>
            <p class="lead mb-4 opacity-90 mx-auto" style="max-width: 650px;">
                Whether you're sharing a ride, offering homemade food, or providing home services —
                orezone connects you with your community. Start with rides, grow with us.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                <Link href="/coming-soon" class="btn btn-light btn-lg text-primary fw-semibold px-5">
                    🔍 Get Started
                </Link>
                <Link href="/coming-soon" class="btn btn-outline-light btn-lg px-5">
                    🚗 Offer a Service
                </Link>
            </div>
            <div class="d-flex justify-content-center gap-4 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">✅</span>
                    <span class="small opacity-75">Free to join</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">✅</span>
                    <span class="small opacity-75">No subscription fees</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">✅</span>
                    <span class="small opacity-75">Verified community</span>
                </div>
            </div>
        </div>
    </section>

    </main>

    <!-- ==================== FOOTER ==================== -->
    <footer class="bg-dark text-white py-5">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h4 class="fw-bold mb-3">orezone</h4>
                    <p class="text-white-50">
                        A community-powered platform for sharing rides, food, services, and skills.
                        One account, many services — built by the community, for the community.
                    </p>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold text-uppercase mb-3">Services</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><span class="text-white">🚗 Ride Sharing</span> <span class="badge bg-success small">Live</span></li>
                        <li class="mb-2"><span class="text-white-50">🍛 Food Services</span></li>
                        <li class="mb-2"><span class="text-white-50">🏠 Home Services</span></li>
                        <li class="mb-2"><span class="text-white-50">🛒 Marketplace</span></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold text-uppercase mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#why-orezone" class="text-white-50 text-decoration-none">About</a></li>
                        <li class="mb-2"><a href="#trust-safety" class="text-white-50 text-decoration-none">Safety</a></li>
                        <li class="mb-2"><a href="#how-it-works" class="text-white-50 text-decoration-none">How It Works</a></li>
                        <li class="mb-2"><a href="mailto:hello@orezone.hrtime.in" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold text-uppercase mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold text-uppercase mb-3">Get the App</h6>
                    <p class="text-white-50 small">Coming soon to iOS and Android.</p>
                    <div class="d-flex gap-2">
                        <Link href="/coming-soon" class="btn btn-outline-light btn-sm">Get Started</Link>
                    </div>
                </div>
            </div>
            <hr class="border-secondary mt-4 mb-3">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-white-50 small mb-0">&copy; 2026 orezone. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-white-50 small mb-0">Built with ❤️ for community</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button
        v-if="showBackToTop"
        class="btn btn-primary btn-lg rounded-circle shadow position-fixed bottom-0 end-0 mb-4 me-4"
        style="z-index: 1050; width: 48px; height: 48px; line-height: 32px;"
        @click="scrollToTop"
        aria-label="Back to top"
        title="Back to top"
    >
        ↑
    </button>
</template>
