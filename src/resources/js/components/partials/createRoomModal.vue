<template>
    <transition name="modal">
        <div class="modal-mask">
          <div class="modal-wrapper">
            <div class="modal-container">

              <div class="modal-body">
                <slot name="body">
                    <p>部屋名を入力してください</p>
                    <div>
                        <label for="name">名前</label>
                        <input id="name" class="form-control" v-model="name">
                    </div>
                    <div class="text-right mt-2">
                        <button class="btn btn-primary" @click="doSend">作成</button>
                    </div>
                </slot>
              </div>

              <div class="modal-footer">
                <slot name="footer">
                  <button class="btn btn-outline-dark modal-default-button" @click="$emit('close')">
                    X
                  </button>
                </slot>
              </div>
            </div>
          </div>
        </div>
      </transition>
</template>
<script>
export default {
  props: {
      token: String
  },
  data() {
    return {
      name: ''
    }
  },
  methods: {
    doSend() {
      axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
      axios.post(
        '/api/room/create',
        {
          headers: {
            "Content-Type": "application/json"
          },
          'name': this.name
      }).then(function(response) {
        if (response.data.status === 'success') {
          location.href = '/room/'+response.data.uname;
        } else {
            alert(response.data.message);
        }
      }).catch(function(error) {
        console.log(error);
      });

      this.$emit('close');
    }
  }
}
</script>
