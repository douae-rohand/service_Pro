<template>
  <div class="app">
    <h1>Test Laravel + Vue.js</h1>
    
    <button @click="testLaravel" :disabled="loading">
      {{ loading ? 'Chargement...' : ' Tester' }}
    </button>

    <div v-if="message" class="success">
       {{ message }}
    </div>

    <div v-if="error" class="error">
       {{ error }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      message: '',
      error: '',
      loading: false
    }
  },
  methods: {
    async testLaravel() {
      this.loading = true;
      this.message = '';
      this.error = '';

      try {
        const response = await fetch('http://localhost:8000/api/test');
        const data = await response.json();
        this.message = data.message;
      } catch (err) {
        this.error = 'Connexion impossible. Laravel est-il lancé?';
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.app {
  max-width: 500px;
  margin: 50px auto;
  padding: 30px;
  text-align: center;
  font-family: Arial;
}

button {
  padding: 15px 30px;
  font-size: 18px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin: 20px 0;
}

button:disabled {
  background: #ccc;
}

.success {
  padding: 20px;
  background: #d4edda;
  border-radius: 8px;
  color: #155724;
  margin-top: 20px;
}

.error {
  padding: 20px;
  background: #f8d7da;
  border-radius: 8px;
  color: #721c24;
  margin-top: 20px;
}
</style>