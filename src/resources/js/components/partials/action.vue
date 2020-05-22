<template>
<div id="action">
    <button v-if="canShowStartButton()" @click="start()">ゲームスタート</button>
</div>
</template>
<script>
export default {
  props: {
    room: Object,
    user_id: Number,
    room_status_open: Number,
    token: String
  },
  data: function () {
    return {
      is_started: false
    }
  },
  created: function () {
    if (this.room.status === this.room_status_open) {
        this.is_started = false;
    }
  },
  methods: {
    canShowStartButton: function () {
      if (this.room.owner_id === this.user_id) {
        if (!this.is_started) {
          return true;
        }
      }
      return false;
    },
    start: function () {
      axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
      axios.post(
        '/api/sugoroku/start',
        {
          headers: {
            "Content-Type": "application/json"
          },
          'room_id': this.room.id
        }).then(response => {
          if (response.data.status === 'success') {
            console.log('ゲームをスタートしました');
            this.is_started = true;
          } else {
            alert(response.data.message);
          }
        }).catch(function(error) {
          console.log(error);
        });

    }
  }
}
</script>
