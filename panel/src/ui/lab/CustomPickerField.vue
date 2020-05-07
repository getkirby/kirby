<script>
import PickerField from "../components/PickerField.vue";

const api = async () => {
  const response = await fetch('https://raw.githubusercontent.com/mledoze/countries/master/countries.json');
  return response.json();
}

const item = (item) => ({
  id: item.cca3,
  title: item.name.common,
  info: "Capital: " + item.capital[0],
  icon: {
    type: item.flag
  }
});

export default {
  extends: PickerField,
  methods: {
    async getItems(ids) {
      const data = await api();
      return ids.map(id => {
        const index = data.findIndex(item => item.cca3 === id);
        return item(data[index]);
      })
    },
    async getOptions({page, limit, search}) {
      const data = await api();
      let items  = data.map(item);

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
  }
}
</script>
