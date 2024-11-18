<footer>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-4 col-xl-3 my-5">
                <img src="{{ asset("images/logo.png") }}" alt="{{ config('app.name') }}" class="img-fluid" style="max-height: 86px!important;"/>
                <div class="call-now my-3">
                    {{ __("common/footer.call_now") }}:  <span>{{ __("common/footer.phone") }}</span>
                </div>
                <p class="address">{{ __("common/footer.address") ?? __("6391 Elgin St. Celina, Delaware 10299, New York, United States of America") }}</p>
            </div>
            <div class="col-md-8 col-xl-9 my-5">
                <div class="row align-items-center">
                    <div class="col-md-6 col-lg-3">
                        <h4 class="footer-link-heading">{{ __("common/footer.quick_links") }}</h4>
                        <div class="d-block">
                            <a href="" class="footer-link">{{ __("common/footer.about") }}</a>
                            <a href="" class="footer-link active">{{ __("common/footer.contact") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.pricing") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.blogs") }}</a>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <h4 class="footer-link-heading">{{ __("common/footer.candidates") }}</h4>
                        <div class="d-block">
                            <a href="" class="footer-link">{{ __("common/footer.browse_jobs") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.browse_employers") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.candidate_dashboard") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.save_jobs") }}</a>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <h4 class="footer-link-heading">{{ __("common/footer.employers") }}</h4>
                        <div class="d-block">
                            <a href="" class="footer-link">{{ __("common/footer.post_a_job") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.browse_candidates") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.employer_dashboard") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.applications") }}</a>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <h4 class="footer-link-heading">{{ __("common/footer.support") }}</h4>
                        <div class="d-block">
                            <a href="" class="footer-link">{{ __("common/footer.faq") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.privacy") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.terms") }}</a>
                            <a href="" class="footer-link">{{ __("common/footer.customer_support") }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex w-100 lower-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 py-3">
                    <p class="copyrights">@ {{ today()->year }} {{ config('app.name') }}. {{ __("common/footer.all_rights_reserved") }}</p>
                </div>
                <div class="col-md-6 py-3 text-end">
                    <a href="" class="social-links"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="" class="social-links mx-2"><i class="fa-brands fa-youtube"></i></a>
                    <a href="" class="social-links mx-2"><i class="fa-brands fa-instagram"></i></a>
                    <a href="" class="social-links"><i class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>
