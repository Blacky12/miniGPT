<script setup>
defineProps({
  size: {
    type: String,
    default: 'md' // sm, md, lg, xl
  },
  color: {
    type: String,
    default: 'indigo' // indigo, gray, white
  }
})

const sizeClasses = {
  sm: 'w-8 h-8',
  md: 'w-12 h-12',
  lg: 'w-24 h-24',
  xl: 'w-32 h-32'
}

const colorClasses = {
  indigo: 'text-indigo-600',
  gray: 'text-gray-600',
  white: 'text-white'
}
</script>

<template>
  <div class="flex items-center gap-3">
    <svg :class="[sizeClasses[size], colorClasses[color]]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span v-if="$slots.default" :class="[
      'font-bold',
      {
        'text-lg': size === 'sm',
        'text-xl': size === 'md',
        'text-3xl': size === 'lg',
        'text-4xl': size === 'xl',
      },
      colorClasses[color]
    ]">
      <slot />
    </span>
  </div>
</template>
