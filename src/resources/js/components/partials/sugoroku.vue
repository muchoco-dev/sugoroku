<template>
<div id="sugoroku">
    <table class="bg-white table table-borderless w-100">
        <tr>
            <td v-bind:id="n" v-for="n in col_count">
                <div v-if="n === 1">
                    Start
                    <span v-for="piece in setPiece(n)">
                        <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece.color"></i>
                    </span>
                </div>
                <div v-else-if="getSpaceName(n)">
                    {{ getSpaceName(n) }}
                    <span v-for="piece in setPiece(n)">
                        <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece.color"></i>
                    </span>
                </div>
                <div v-else>
                    <span v-for="piece in setPiece(n)">
                        <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece.color"></i>
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td v-bind:id="board.goal_position">
                Goal
                <span v-for="piece in setPiece(board.goal_position)">
                    <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece.color"></i>
                </span>
            </td>
            <td border="0" v-for="n in (col_count-2)" class="bg-light">
                &nbsp;
            </td>
            <td v-bind:id="col_count+1">
                {{ getSpaceName(col_count+1) }}
                <span v-for="piece in setPiece(col_count+1)">
                    <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece.color"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td v-for="n in col_count" v-bind:id="board.goal_position - n">
                {{ getSpaceName(board.goal_position - n) }}
                <span v-for="piece in setPiece(board.goal_position - n)">
                    <i v-bind:class="'fas fa-2x fa-' + piece.aicon + ' ' + piece.color"></i>
                </span>
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
      spaces: Object
  },
  data() {
    return {
      col_count: 0,
      piece_icons: ['cat', 'crow', 'frog', 'dragon', 'dog'],
      virus_icon: 'virus',
      piece_colors: {
        health: 'text-success',
        sick: 'text-danger',
        finished:  ''
      },
      pieces: [],
      piece_positions: {},
    }
  },
  created: function () {
      this.col_count = (Number(this.board.goal_position) - 2) / 2;

      this.piece_positions = {
        1: [
            {
                user_id: 1,
                aicon: this.piece_icons[0],
                color: this.piece_colors.health
            },
            {
                user_id: 2,
                aicon: this.piece_icons[4],
                color: this.piece_colors.health
            },
            {
                user_id: 0,
                aicon: this.virus_icon,
                color: this.piece_colors.sick
            }
        ]
      }; // TODO: 他の実装に合わせてデータ構成調整
   },
  methods: {
    getSpaceName: function (id) {
        if (this.spaces[id]) {
            return this.spaces[id].name;
        }
        return '';
    },
    setPiece: function (position) {
        return this.piece_positions[position];
    },
    movePiece: function (user_id, move_num) {
        let piece_positions_tmp = {};
        for (let position in this.piece_positions) {
            let users = this.piece_positions[position];
            for (let key in users) {
                if (user_id === users[key]['user_id']) {
                    let new_position = parseInt(position) + parseInt(move_num);
                    if (new_position > this.board.goal_position) {
                        new_position = new_position - this.board.goal_position;
                    }
                    if (!Array.isArray(piece_positions_tmp[new_position])) {
                        piece_positions_tmp[new_position] = [];
                    }
                    piece_positions_tmp[new_position].push(users[key]);
                } else {
                    if (!Array.isArray(piece_positions_tmp[position])) {
                        piece_positions_tmp[position] = [];
                    }
                    piece_positions_tmp[position].push(users[key]);
                }
            }
        }
        this.piece_positions = piece_positions_tmp;
    }
  }
}
</script>
