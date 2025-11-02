<template>
  <div>
    <div class="user-list">
    <User
        v-for="u in users"
        :key="u.id"
        :user="u"
      />
    </div>
    <details v-if="alumni && alumni.length > 0">
      <summary>Alumni</summary>
        <div class="user-list">
          <User
            v-for="u in alumni"
            :key="u.id"
            :user="u"
          />
        </div>
      </details>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import User from './components/User.vue'
const users = ref([])
const alumni = ref([])

onMounted(async () => {
  // Get API URL from WordPress or fallback to current origin
  const apiUrl = window.teamdisplayConfig?.apiUrl || '/wp-json/teamdisplay/v1/intros'
  const res = await fetch(apiUrl)
  const data = await res.json()
  users.value = data.filter(user => user.status === 'aktiv')
  alumni.value = data.filter(user => user.status === 'alumni')
})

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}
</script>

<style scoped lang="scss">
.user-list {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: stretch;
    justify-content: space-around;
}
</style>