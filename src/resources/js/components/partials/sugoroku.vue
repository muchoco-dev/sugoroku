<template>
<div id="sugoroku">
    <table border="1">
        <tr>
            <td v-bind:id="n" v-for="n in col_count">
                <div v-if="n === 1">Start</div>
                <div v-else-if="getSpaceName(n)">{{ getSpaceName(n) }}</div>
                <div v-else>&nbsp;</div>
            </td>
        </tr>
        <tr>
            <td v-bind:id="col_count">Goal</td>
            <td border="0" v-for="n in (col_count-2)">
                &nbsp;
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td v-for="n in col_count">
                &nbsp;
            </td>
        </tr>
    </table>
</div>
</template>
<script>
export default {
  props: {
      board: Object,
      spaces_str: String
  },
  data() {
    return {
        col_count: 0,
        spaces: []
    }
  },
  created: function () {
      this.spaces = JSON.parse(this.spaces_str);
      this.col_count = (Number(this.board.goal_position) - 2) / 2;
  },
  methods: {
    getSpaceName: function (id) {
        console.log(this.spaces[id]);
        if (this.spaces[id]) {
            return this.spaces[id].name;
        }
        return false;
    }
  }
}
</script>
