<template>
  <div class="k-panel">
    <header class="k-topbar">
      <nav class="k-topbar-left">

      </nav>
      <nav class="k-topbar-center pl-2">
        <k-breadcrumb :links="breadcrumb" />
      </nav>
      <nav class="k-topbar-right">
        <k-button class="k-topbar-button" icon="search" />
      </nav>
    </header>

    <nav class="k-sidebar">

    </nav>
    <main class="k-mainbar p-8">


      <!-- <k-header>
        Photography
        <k-button-group slot="left">
          <k-button icon="open">Open</k-button>
          <k-button icon="cog" @click="$refs.drawer.open()">Settings</k-button>
        </k-button-group>
      </k-header> -->

      <section class="mb-8">
        <k-headline class="mb-3">Drafts</k-headline>
        <k-items
          :items="items.slice(0, 3)"
          layout="list"
        />
      </section>

      <section class="mb-8">
        <k-headline class="mb-3">Published</k-headline>
        <k-items
          :items="items"
          layout="cards"
        />
      </section>

    </main>

    <div class="k-infobar">

    </div>

  </div>
</template>

<script>
export default {
  data() {
    return {
      items: [...Array(20).keys()].map(item => {
        return {
          title: "List item no. " + item,
          info: "List item info",
          link: "https://getkirby.com",
          image: {
            url: "https://source.unsplash.com/user/erondu/1600x900?" + item,
            cover: true,
          },
          options: [
            { icon: "edit", text: "Edit", click: "edit" },
            { icon: "trash", text: "Delete", click: "delete" }
          ]
        };
      })
    };
  },
  computed: {
    breadcrumb() {
      return [
        { link: "https://getkirby.com", text: "Maegazine" },
        { link: "https://getkirby.com/docs", text: "Photography" },
      ];
    }
  }
}
</script>

<style lang="scss">

body {
  overflow: hidden;
}

.k-panel {
  display: grid;
  grid-template-columns: 12rem auto 12rem;
  grid-template-rows: 2.5rem auto;
  grid-template-areas:
    "topbar topbar topbar"
    "sidebar mainbar infobar";
  overflow: hidden;
}


.k-topbar-left {
  border-right: 1px solid #222;
}
.k-topbar-right {
  display: flex;
  justify-content: flex-end;
  border-left: 1px solid #222;
}

.k-sidebar {
  background: #fff;
  grid-area: sidebar;
}

.k-mainbar {
  grid-area: mainbar;
  overflow-y: auto;
  overflow-x: hidden;
}

.k-infobar {
  background: #fff;
  grid-area: infobar;
}

</style>
