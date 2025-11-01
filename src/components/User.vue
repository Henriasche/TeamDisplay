<template>
  <div class="p-4 shadow rounded">
      <div class="flex items-center space-x-4">
        <img :src="user.avatar" class="w-16 h-16 rounded-full" />
        <div>
          <h3 class="font-semibold">{{ user.name }}</h3>
          <p class="text-sm text-gray-600">Status: {{ user.status }}</p>
        </div>
      </div>

      <p class="mt-2">{{ user.intro }}</p>

      <div v-if="user.periods && user.periods.length" class="mt-2 text-sm text-gray-600">
        <h4 class="font-semibold mb-1">Aktivitätszeiträume:</h4>
        <ul>
          <li v-for="(p, i) in user.periods" :key="i">
            <strong>{{ formatDate(p.from) }} – {{ formatDate(p.to) || 'heute' }}</strong><br>
            <span v-if="p.activity">{{ p.activity }}</span>
          </li>
        </ul>
      </div>
    </div>
</template>

<script setup lang="ts">
import IUser from '@/interfaces/IUser'

const props = defineProps({
  user:{
    type: Object as IUser,
    required: true
  }
})

function formatDate(date: string | number | Date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}
</script>