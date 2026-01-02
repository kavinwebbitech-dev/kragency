@extends('frontend.layouts.app')

@section('title', 'Home')

@section('content')
    <section class="rules-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-header text-center mb-4">
                        <h2 class="title">Game Rules</h2>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="rules-content">
                        <h4>1. Eligibility</h4>
                        <p>Participants must be at least 18 years old and comply with local laws regarding lottery participation.</p>

                        <h4>2. Ticket Purchase</h4>
                        <p>Tickets can be purchased online through our platform. Each ticket costs $1. Participants can choose their own numbers or opt for a quick pick.</p>

                        <h4>3. Game Format</h4>
                        <p>The game consists of selecting 3 or 4 digits from 000 to 9999. Draws are held twice daily, and winning numbers are randomly selected.</p>

                        <h4>4. Winning Combinations</h4>
                        <p>Prizes are awarded based on matching the drawn numbers in exact order or any order, depending on the bet type chosen.</p>

                        <h4>5. Prize Distribution</h4>
                        <p>Winnings are credited to the participant's account within 24 hours of the draw. Participants can withdraw their winnings at any time.</p>

                        <h4>6. Responsible Gaming</h4>
                        <p>We promote responsible gaming and encourage participants to play within their means. If you feel you may have a gambling problem, please seek help.</p>

                        <h4>7. Terms and Conditions</h4>
                        <p>By participating in the game, participants agree to abide by all rules and regulations set forth by our platform. We reserve the right to modify the rules at any time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



@endsection

@push('scripts')
<script>
    
</script>
@endpush
