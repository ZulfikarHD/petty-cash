<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div class="w-full overflow-auto">
    <table
      data-slot="table"
      :class="cn('w-full caption-bottom text-sm border-separate border-spacing-0', props.class)"
    >
      <slot />
    </table>
  </div>
</template>
