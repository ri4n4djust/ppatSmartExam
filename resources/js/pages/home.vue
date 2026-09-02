<script setup>
import logo from '@images/logoippat.png';

const menuItems = [
  { title: 'Home', href: '#home' },
  { title: 'Features', href: '#features' },
  { title: 'About', href: '#about' },
]
</script>

<template>
  <div class="guest-page">
    <header class="guest-navbar">
      <RouterLink to="/" class="brand" aria-label="PPAT SMART EXAM">
        <!-- eslint-disable vue/no-v-html -->
        <img
          :src="logo"
          alt="PPAT SMART EXAM logo"
          class="app-logo-image"
        >
        <!-- eslint-enable vue/no-v-html -->
        <span>PPAT SMART EXAM</span>
      </RouterLink>

      <nav class="guest-menu d-none d-md-flex" aria-label="Main navigation">
        <a v-for="item in menuItems" :key="item.href" :href="item.href">{{ item.title }}</a>
      </nav>

      <div class="guest-actions">
        <VBtn class="d-none d-sm-inline-flex" to="/login" variant="text">Sign in</VBtn>
        <VBtn to="/register" color="primary">Get started</VBtn>
      </div>
    </header>

    <main id="home">
      <section class="hero">
        <div class="hero-copy">
          <p class="eyebrow">Simulasi Ujian</p>
          <h2>Persiapkan diri Anda</h2>
          <p class="hero-description">
            PPAT SMART EXAM memberikan anda materi dan simulasi ujian yang lengkap.
          </p>
          <div class="hero-actions">
            <VBtn to="/register" color="primary" size="large">Create a free account</VBtn>
            <VBtn to="/login" size="large" variant="outlined">Sign in</VBtn>
          </div>
        </div>

        <div class="hero-panel">
          <p class="panel-label">Total bergabung</p>
          <div class="progress-row"><span>Lebih dari 1000 pengguna</span><strong>bergabung</strong></div>
          <VProgressLinear color="primary" height="8" model-value="80" rounded />
          <div class="task-card">
            <VIcon color="success" icon="ri-checkbox-circle-line" />
            <span>Invite your team</span>
          </div>
          <div class="task-card pending">
            <VIcon icon="ri-time-line" />
            <span>Plan next milestone</span>
          </div>
        </div>
      </section>

      <section id="features" class="features">
        <div>
          <p class="eyebrow">Everything in one place</p>
          <h2>Bagaimana Memulai.</h2>
        </div>
        <div class="feature-grid">
          <article>
            <VIcon icon="ri-layout-grid-line" />
            <h3>Dapatkan Materi</h3>
            <p>See what matters now, without digging through scattered tools.</p>
          </article>
          <article>
            <VIcon icon="ri-team-line" />
            <h3>Lakukan Ujian</h3>
            <p>Keep everyone aligned with shared workspaces and simple updates.</p>
          </article>
          <article>
            <VIcon icon="ri-line-chart-line" />
            <h3>Menjadi lebih baik</h3>
            <p>Turn day-to-day progress into reliable, useful insight.</p>
          </article>
        </div>
      </section>

      <section id="about" class="about">
        <div>
          <p class="eyebrow">Made for focused</p>
          <h2>Dibuat untuk meningkatkan potensi anda.</h2>
        </div>
        <VBtn to="/register" color="primary" size="large">Start for free</VBtn>
      </section>
    </main>
  </div>
</template>

<style lang="scss" scoped>

.guest-page {
  min-block-size: 100dvh;
  background: rgb(var(--v-theme-background));
  color: rgb(var(--v-theme-on-background));
}

.guest-navbar {
  position: sticky;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-block-size: 4.75rem;
  padding-inline: clamp(1.25rem, 5vw, 5rem);
  background: rgb(var(--v-theme-surface) / 94%);
  border-block-end: 1px solid rgb(var(--v-theme-on-surface) / 8%);
  box-shadow: 0 2px 10px rgb(0 0 0 / 4%);
  inset-block-start: 0;
}

.brand, .guest-menu, .guest-actions, .hero-actions { display: flex; align-items: center; }

.brand {
  gap: 0.65rem;
  color: inherit;
  font-size: 1.25rem;
  font-weight: 700;
  text-decoration: none;

  .app-logo-image {
    block-size: 2.75rem;
    inline-size: 2.75rem;
    object-fit: contain;
  }

  :deep(svg) { block-size: 2rem; inline-size: 2rem; }
}

.guest-menu {
  gap: 2rem;

  a {
    color: rgb(var(--v-theme-on-surface) / 68%);
    font-size: 0.9375rem;
    font-weight: 500;
    text-decoration: none;

    &:hover { color: rgb(var(--v-theme-primary)); }
  }
}

.guest-actions, .hero-actions { gap: 0.75rem; }

.hero, .features, .about {
  max-inline-size: 75rem;
  margin-inline: auto;
  padding-inline: clamp(1.25rem, 5vw, 5rem);
}

.hero {
  display: grid;
  gap: clamp(3rem, 8vw, 8rem);
  grid-template-columns: minmax(0, 1.1fr) minmax(18rem, 0.9fr);
  align-items: center;
  min-block-size: calc(100dvh - 4.75rem);
  padding-block: 5rem;
}

.eyebrow {
  margin-block-end: 1rem;
  color: rgb(var(--v-theme-primary));
  font-size: 0.8125rem;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
}

h1, h2, h3, p { margin: 0; }
h1 { max-inline-size: 42rem; font-size: clamp(2.75rem, 6vw, 5rem); letter-spacing: -0.055em; line-height: 1.03; }
h2 { font-size: clamp(2rem, 4vw, 3.25rem); letter-spacing: -0.04em; line-height: 1.1; }

.hero-description {
  max-inline-size: 36rem;
  margin-block: 1.5rem 2rem;
  color: rgb(var(--v-theme-on-surface) / 70%);
  font-size: 1.125rem;
  line-height: 1.7;
}

.hero-panel {
  padding: 2rem;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgb(var(--v-theme-on-surface) / 8%);
  border-radius: 1.5rem;
  box-shadow: 0 1.5rem 3.75rem rgb(0 0 0 / 10%);
}

.panel-label { margin-block-end: 1.75rem; color: rgb(var(--v-theme-on-surface) / 60%); font-size: 0.875rem; font-weight: 600; }
.progress-row { display: flex; justify-content: space-between; margin-block-end: 0.75rem; font-size: 0.9375rem; }

.task-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-block-start: 1rem;
  padding: 1rem;
  background: rgb(var(--v-theme-on-surface) / 4%);
  border-radius: 0.75rem;
  font-size: 0.9375rem;
  font-weight: 600;

  &.pending .v-icon { color: rgb(var(--v-theme-warning)); }
}

.features { padding-block: 6rem; }
.feature-grid {
  display: grid;
  gap: 1.25rem;
  grid-template-columns: repeat(3, 1fr);
  margin-block-start: 3rem;

  article { padding: 1.75rem; background: rgb(var(--v-theme-surface)); border: 1px solid rgb(var(--v-theme-on-surface) / 8%); border-radius: 1rem; }
  .v-icon { margin-block-end: 1.5rem; color: rgb(var(--v-theme-primary)); font-size: 1.5rem; }
  h3 { font-size: 1.125rem; }
  p { margin-block-start: 0.625rem; color: rgb(var(--v-theme-on-surface) / 65%); line-height: 1.6; }
}

.about {
  display: flex;
  gap: 2rem;
  align-items: center;
  justify-content: space-between;
  margin-block: 2rem 5rem;
  padding-block: 3rem;
  background: rgb(var(--v-theme-primary) / 8%);
  border-radius: 1.5rem;
}

@media (max-width: 959px) {
  .hero, .feature-grid { grid-template-columns: 1fr; }
}

@media (max-width: 599px) {
  .guest-navbar { padding-inline: 1.25rem; }
  .hero { padding-block: 3.5rem; }
  .hero-actions, .about { align-items: stretch; flex-direction: column; }
  .hero-actions .v-btn, .about .v-btn { inline-size: 100%; }
}
</style>
