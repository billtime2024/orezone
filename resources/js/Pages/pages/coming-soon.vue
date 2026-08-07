<script>
import { Head } from '@inertiajs/vue3'

export default {
  components: { Head },
  data() {
    return {
      targetDate: new Date('2027-01-01T00:00:00').getTime(),
      days: 0,
      hours: 0,
      minutes: 0,
      seconds: 0,
      ended: false,
      email: '',
      submitted: false,
    }
  },
  mounted() {
    this.updateCountdown()
    this.timer = setInterval(this.updateCountdown, 1000)
  },
  beforeUnmount() {
    clearInterval(this.timer)
  },
  methods: {
    updateCountdown() {
      const now = Date.now()
      const distance = this.targetDate - now
      if (distance <= 0) {
        this.ended = true
        this.days = 0
        this.hours = 0
        this.minutes = 0
        this.seconds = 0
        clearInterval(this.timer)
        return
      }
      this.days = Math.floor(distance / (1000 * 60 * 60 * 24))
      this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
      this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
      this.seconds = Math.floor((distance % (1000 * 60)) / 1000)
    },
    handleSubmit() {
      if (this.email) {
        this.submitted = true
        this.email = ''
      }
    },
  },
}
</script>

<template>
  <Head title="Coming Soon | Orezone" />

  <div class="auth-page-wrapper pt-5">
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
      <div class="bg-overlay"></div>
      <div class="shape">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
          viewBox="0 0 1440 120">
          <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
        </svg>
      </div>
    </div>

    <div class="auth-page-content">
      <BContainer>
        <BRow>
          <BCol lg="12">
            <div class="text-center mt-sm-5 pt-4 mb-4">
              <div class="mb-4">
                <h1 class="display-2 coming-soon-text fw-bold">Orezone</h1>
                <p class="text-muted fs-5">Community Sharing Platform</p>
              </div>

              <div class="mb-3">
                <h4 class="coming-soon-text">Coming Soon</h4>
              </div>

              <div v-if="!ended" class="mt-4">
                <BRow class="justify-content-center">
                  <BCol lg="8">
                    <div class="countdownlist d-flex justify-content-center gap-3 flex-wrap">
                      <div class="countdownlist-item">
                        <div class="count-title">Days</div>
                        <div class="count-num">{{ String(days).padStart(2, '0') }}</div>
                      </div>
                      <div class="countdownlist-item">
                        <div class="count-title">Hours</div>
                        <div class="count-num">{{ String(hours).padStart(2, '0') }}</div>
                      </div>
                      <div class="countdownlist-item">
                        <div class="count-title">Minutes</div>
                        <div class="count-num">{{ String(minutes).padStart(2, '0') }}</div>
                      </div>
                      <div class="countdownlist-item">
                        <div class="count-title">Seconds</div>
                        <div class="count-num">{{ String(seconds).padStart(2, '0') }}</div>
                      </div>
                    </div>
                  </BCol>
                </BRow>
              </div>

              <div v-else class="mt-4">
                <p class="text-success fs-4 fw-semibold">We have launched! 🎉</p>
              </div>

              <div class="mt-4">
                <h5>Get notified when we launch</h5>
                <p class="text-muted">Don't worry, we will not spam you 😊</p>

                <div v-if="submitted" class="alert alert-success mx-auto" style="max-width:420px">
                  Thanks! We'll notify you at launch.
                </div>

                <form v-else @submit.prevent="handleSubmit" class="input-group countdown-input-group mx-auto my-3" style="max-width:420px">
                  <input
                    v-model="email"
                    type="email"
                    class="form-control border-light shadow"
                    placeholder="Enter your email address"
                    required
                    aria-label="Email address"
                  />
                  <BButton variant="success" type="submit">
                    Send<i class="ri-send-plane-2-fill align-bottom ms-2"></i>
                  </BButton>
                </form>
              </div>
            </div>
          </BCol>
        </BRow>
      </BContainer>
    </div>

    <footer class="footer">
      <BContainer>
        <BRow>
          <BCol lg="12">
            <div class="text-center">
              <p class="mb-0 text-muted">
                &copy; {{ new Date().getFullYear() }} orezone. All rights reserved.
              </p>
            </div>
          </BCol>
        </BRow>
      </BContainer>
    </footer>
  </div>
</template>
