<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel } from '@headlessui/vue';
import { XMarkIcon, PaperClipIcon, ChevronLeftIcon, ChevronRightIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/solid';
import { isImage } from '@/helpers';

const props = defineProps({
  attachments: { type: Array, required: true },
  index: Number,
  modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue', 'update:index', 'hide']);

const show = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const currentIndex = computed({
  get: () => props.index,
  set: (value) => emit('update:index', value),
});

const attachment = computed(() => props.attachments[currentIndex.value] || null);
const total = computed(() => props.attachments.length);

const isVideo = (att) => att && /\.(mp4|webm|ogg|mov)$/i.test(att.url || att.name || '');
const isImageType = (att) => att && isImage(att);

const zoomed = ref(false);
const zoomStyle = ref('');
const videoRef = ref(null);
const showControls = ref(true);
let hideTimer = null;

function resetControls() {
  showControls.value = true;
  clearTimeout(hideTimer);
  hideTimer = setTimeout(() => { showControls.value = false; }, 2800);
}

function closeModal() {
  show.value = false;
  zoomed.value = false;
  emit('hide');
}

function prev() {
  if (currentIndex.value === 0) return;
  currentIndex.value--;
  zoomed.value = false;
}

function next() {
  if (currentIndex.value >= total.value - 1) return;
  currentIndex.value++;
  zoomed.value = false;
}

function toggleZoom(e) {
  if (!isImageType(attachment.value)) return;
  if (zoomed.value) {
    zoomed.value = false;
    zoomStyle.value = '';
  } else {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    zoomStyle.value = `transform-origin: ${x}% ${y}%`;
    zoomed.value = true;
  }
}

function onKey(e) {
  if (!show.value) return;
  if (e.key === 'ArrowLeft') prev();
  if (e.key === 'ArrowRight') next();
  if (e.key === 'Escape') closeModal();
}

onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => { window.removeEventListener('keydown', onKey); clearTimeout(hideTimer); });

watch(show, (v) => { if (v) { zoomed.value = false; resetControls(); } });

function downloadAttachment() {
  if (!attachment.value?.url) return;
  const a = document.createElement('a');
  a.href = attachment.value.url;
  a.download = attachment.value.name || 'file';
  a.click();
}
</script>

<template>
  <teleport to="body">
    <TransitionRoot appear :show="show" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50">

        <!-- Backdrop -->
        <TransitionChild
          as="template"
          enter="duration-400 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-300 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="viewer-backdrop fixed inset-0" @mousemove="resetControls" />
        </TransitionChild>

        <div class="fixed inset-0 z-50" @mousemove="resetControls">
          <TransitionChild
            as="template"
            enter="duration-400 ease-out"
            enter-from="opacity-0 scale-[0.97]"
            enter-to="opacity-100 scale-100"
            leave="duration-300 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-[0.97]"
          >
            <DialogPanel class="viewer-panel fixed inset-0 flex flex-col">

             
              <div
                class="viewer-topbar absolute top-0 left-0 right-0 z-20 flex items-center justify-between px-5 py-4 transition-opacity duration-500"
                :class="showControls ? 'opacity-100' : 'opacity-0'"
              >
                <div class="flex items-center gap-3">
                  <div class="file-icon-badge">
                    <PaperClipIcon v-if="!isImageType(attachment) && !isVideo(attachment)" class="w-4 h-4 text-white" />
                    <svg v-else-if="isVideo(attachment)" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                      <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>
                    </svg>
                  </div>
                  <div>
                    <p class="file-name">{{ attachment?.name }}</p>
                    <p class="file-meta">{{ currentIndex + 1 }} / {{ total }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button class="icon-btn" @click="downloadAttachment" title="Télécharger">
                    <ArrowDownTrayIcon class="w-5 h-5" />
                  </button>
                  <button class="icon-btn close-btn" @click="closeModal" title="Fermer">
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>
              </div>

             
              <div class="flex-1 relative flex items-center justify-center overflow-hidden">

               
                <button
                  @click="prev"
                  class="nav-btn nav-prev transition-opacity duration-500"
                  :class="[showControls ? 'opacity-100' : 'opacity-0', currentIndex === 0 ? 'invisible' : '']"
                >
                  <ChevronLeftIcon class="w-6 h-6 text-white" />
                </button>

               
                <div
                  v-if="isImageType(attachment)"
                  class="media-container"
                  @click="toggleZoom"
                  :class="{ 'cursor-zoom-in': !zoomed, 'cursor-zoom-out': zoomed }"
                >
                  <img
                    :src="attachment.url"
                    :alt="attachment.name"
                    :style="zoomed ? zoomStyle : ''"
                    :class="['viewer-image', { 'zoomed': zoomed }]"
                  />
                </div>

                
                <div v-else-if="isVideo(attachment)" class="media-container">
                  <video
                    ref="videoRef"
                    :src="attachment.url"
                    class="viewer-video"
                    controls
                    autoplay
                    playsinline
                  />
                </div>

                
                <div v-else class="file-preview-card">
                  <div class="file-preview-icon">
                    <PaperClipIcon class="w-14 h-14 text-white/60 font-bold" />
                  </div>
                  <p class="file-preview-name font-bold">{{ attachment?.name }}</p>
                  <button class="download-btn" @click="downloadAttachment">
                    <ArrowDownTrayIcon class="w-5 h-5" />
                    Download
                  </button>
                </div>

                
                <button
                  @click="next"
                  class="nav-btn nav-next transition-opacity duration-500"
                  :class="[showControls ? 'opacity-100' : 'opacity-0', currentIndex >= total - 1 ? 'invisible' : '']"
                >
                  <ChevronRightIcon class="w-6 h-6 text-white" />
                </button>
              </div>

              <div
                v-if="total > 1"
                class="viewer-thumbnails transition-opacity duration-500"
                :class="showControls ? 'opacity-100' : 'opacity-0'"
              >
                <button
                  v-for="(att, i) in attachments"
                  :key="i"
                  @click="currentIndex = i; zoomed = false"
                  :class="['thumb-btn', { 'thumb-active': i === currentIndex }]"
                >
                  <img v-if="isImage(att)" :src="att.url" class="thumb-img" />
                  <div v-else class="thumb-file">
                    <PaperClipIcon class="w-4 h-4 text-white/70" />
                  </div>
                </button>
              </div>

            </DialogPanel>
          </TransitionChild>
        </div>

      </Dialog>
    </TransitionRoot>
  </teleport>
</template>

<style scoped>
/* ── Backdrop ── */
.viewer-backdrop {
  background: rgba(50, 57, 68, 0.492); /* Tailwind slate-800 avec opacity */
  backdrop-filter: blur(24px) saturate(0.6);
}
/* ── Panel ── */
.viewer-panel {
  background: transparent;
}

/* ── Top Bar ── */
.viewer-topbar {
  background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%);
}

.file-icon-badge {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(8px);
}

.file-name {
  font-family: 'SF Pro Display', 'Helvetica Neue', sans-serif;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255,255,255,0.95);
  max-width: 280px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-meta {
  font-size: 0.72rem;
  color: rgba(255,255,255,0.45);
  font-variant-numeric: tabular-nums;
  letter-spacing: 0.04em;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255,255,255,0.8);
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.1);
  backdrop-filter: blur(12px);
  transition: all 0.2s ease;
}

.icon-btn:hover {
  background: rgba(255,255,255,0.18);
  color: white;
  transform: scale(1.06);
}

.close-btn:hover {
  background: rgba(189, 8, 8, 0.55);
  border-color: rgba(220, 60, 60, 0.3);
}

/* ── Navigation Buttons ── */
.nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 20;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.15);
  backdrop-filter: blur(12px);
  transition: all 0.2s ease;
}

.nav-btn:hover {
  background: rgba(255,255,255,0.22);
  transform: translateY(-50%) scale(1.08);
}

.nav-prev { left: 20px; }
.nav-next { right: 20px; }

/* ── Media Container ── */
.media-container {
  max-width: calc(100vw - 140px);
  max-height: calc(100vh - 160px);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 6px;
}

/* ── Image ── */
.viewer-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 4px;
  transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              box-shadow 0.35s ease;
  box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04);
  user-select: none;
  display: block;
}

.viewer-image.zoomed {
  transform: scale(2.5);
}

/* ── Video ── */
.viewer-video {
  max-width: calc(100vw - 140px);
  max-height: calc(100vh - 180px);
  border-radius: 8px;
  box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);
  background: #000;
}

/* ── File preview card ── */
.file-preview-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 56px 72px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.09);
  border-radius: 20px;
  backdrop-filter: blur(24px);
  box-shadow: 0 32px 80px rgba(0,0,0,0.4);
}

.file-preview-icon {
  width: 88px;
  height: 88px;
  border-radius: 22px;
  background: rgba(255,255,255,0.07);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255,255,255,0.1);
}

.file-preview-name {
  font-family: 'SF Pro Display', 'Helvetica Neue', sans-serif;
  font-size: 1rem;
  font-weight: 500;
  color: rgba(255,255,255,0.85);
  max-width: 320px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.download-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  border-radius: 50px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.18);
  color: white;
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.02em;
  transition: all 0.2s ease;
  backdrop-filter: blur(8px);
}

.download-btn:hover {
  background: rgba(255,255,255,0.22);
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

/* ── Thumbnail strip ── */
.viewer-thumbnails {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  padding: 16px 20px 20px;
  background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
}

.thumb-btn {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid transparent;
  transition: all 0.2s ease;
  opacity: 0.55;
  flex-shrink: 0;
}

.thumb-btn:hover {
  opacity: 0.85;
  transform: scale(1.08);
}

.thumb-active {
  border-color: white;
  opacity: 1;
  transform: scale(1.1);
  box-shadow: 0 4px 16px rgba(0,0,0,0.5);
}

.thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb-file {
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>