<template>
  <div>
    <div class="user-list">
    <User
        v-for="u in users"
        :key="u.id"
        :user="u"
      />
    </div>

    <v-expansion-panels>
      <v-expansion-panel v-if="alumni && alumni.length > 0">
        <v-expansion-panel-title collapse-icon="mdi-minus" expand-icon="mdi-plus">
          <h2 class="accordion-header">Alumni</h2>
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <div class="user-list">
            <User
              v-for="u in alumni"
              :key="u.id"
              :user="u"
            />
          </div>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>
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
.v-expansion-panels {
    z-index: auto;
}
.v-expansion-panel {
    background-color: transparent;

    .v-expansion-panel-title {
      background-color: rgba(var(--v-theme-surface), 0.8);
    }

    .v-expansion-panel-text {
      background-color: rgba(var(--v-theme-surface), 0.15);
    }
.accordion-header {
        margin-bottom: 0;
        font-weight: 700;
    }
}
</style>