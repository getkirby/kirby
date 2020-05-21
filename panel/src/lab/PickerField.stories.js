import Padding from "../../storybook/theme/Padding.js";
import PickerFieldFoundation from "@/app/components/PickerFieldFoundation.vue";

export default {
  title: "Lab | Picker Field",
  decorators: [Padding]
};

export const Countries = () => ({
  components: {
    "k-custom-picker-field": {
      extends: PickerFieldFoundation,
      computed: {
        api() {
          return async () => {
            const response = await fetch("https://raw.githubusercontent.com/mledoze/countries/master/countries.json");
            return response.json();
          };
        }
      },
      methods: {
        /**
         * Return options array for a list of ids
         */
        async getItems(ids) {
          const data = await this.api();
          return ids.map(id => {
            const index = data.findIndex(item => item.cca3 === id);
            return this.item(data[index]);
          })
        },
        /**
         * Return options array for all available items
         * (and paginate and filter them)
         */
        async getOptions({page, limit, search}) {
          const data = await this.api();
          let items  = data.map(this.item);

          if (search) {
            items = items.filter(item => item.title.toLowerCase().includes(search.toLowerCase()));
          }

          const offset = (page - 1) * limit;
          const paginated = items.slice(offset, offset + limit);

          return {
            data: paginated,
            pagination: {
              page: page,
              limit: limit,
              total: items.length
            }
          }
        },
        item(data) {
          return {
            id: data.cca3,
            title: data.name.common,
            info: "Capital: " + data.capital[0],
            icon: {
              type: data.flag
            }
          };
        }
      }
    }
  },
  data() {
    return {
      value: ["VEN", "HND"]
    }
  },
  template: `
    <div>
      <k-custom-picker-field
        v-model="value"
        label="Bucket list"
      />
      <k-headline class="mt-8 mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const Movies = () => ({
  components: {
    "k-custom-picker-field": {
      extends: PickerFieldFoundation,
      computed: {
        apiKey() {
          return "965697b4f94821082f5e099332e12ff1";
        }
      },
      methods: {
        /**
         * Return options array for a list of ids
         */
        async getItems(ids) {
          const calls = ids.map(async id => {
            const response = await fetch(`https://api.themoviedb.org/3/movie/${id}?api_key=${this.apiKey}`);
            const data = await response.json();
            return this.item(data);
          });
          return Promise.all(calls);
        },
        /**
         * Return options array for all available items
         * (and paginate and filter them)
         */
        async getOptions({page, limit, search}) {
          let items, data;

          if (search) {
            const response = await fetch(`https://api.themoviedb.org/3/search/movie?api_key=${this.apiKey}&include_adult=false&query=${search}&page=${page}`);
            data = await response.json();
            items = await this.getItems(data.results.map(item => item.id));

          } else {
            const response = await fetch(`https://api.themoviedb.org/3/discover/movie?api_key=${this.apiKey}&sort_by=popularity.desc&include_adult=false&include_video=false&page=${page}`);
            data  = await response.json();
            items = data.results.map(this.item);
          }

          return {
            data: items,
            pagination: {
              page: page,
              limit: Math.ceil(data.total_results / data.total_pages),
              total: data.total_results
            }
          }
        },
        item(data) {
          return {
            id: data.id,
            title: data.title,
            info: `Rating: ${data.vote_average || '–'}`,
            image: {
              url: "http://image.tmdb.org/t/p/w500/" + data.poster_path,
              ratio: "11.333/17"
            }
          };
        }
      },
    }
  },
  data() {
    return {
      value: []
    }
  },
  template: `
    <div>
      <k-custom-picker-field
        v-model="value"
        label="Watchlist"
        :empty="{
          icon: 'video',
          text: 'Pick a movie and start binging'
        }"
        :image="{ cover: true }"
        layout="cardlets"
        :picker="{
          items: { layout: 'cards', size: 'small' },
          size: 'large'
        }"
      />
      <k-headline class="mt-8 mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
})
