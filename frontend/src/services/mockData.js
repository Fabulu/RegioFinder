// src/services/mockData.js
// Customer-view mock data: 3 shops with coordinates + posts

export const mockShops = [
  {
    id: 1,
    account: {
      displayName: "Anna Hoyer",
      shopName: "Hofladen Müller",
    },
    profile: {
      name: "Hofladen Müller",
      type: "Hofladen",
      phone: "+41 79 111 22 33",
      link: "https://example.com/hofladen-mueller",
      address: "Hauptstrasse 12, 5200 Brugg",
      lat: "47.482140",
      lon: "8.208720",
      hours: {
        mon: { closed: false, from: "09:00", to: "18:00" },
        tue: { closed: false, from: "09:00", to: "18:00" },
        wed: { closed: false, from: "09:00", to: "18:00" },
        thu: { closed: false, from: "09:00", to: "18:00" },
        fri: { closed: false, from: "09:00", to: "18:00" },
        sat: { closed: false, from: "09:00", to: "12:00" },
        sun: { closed: true, from: "", to: "" },
      },
      vacation: { enabled: false, from: "", to: "", note: "" },
    },
    sortiment: [
      { id: "p1", name: "Eier", expiresOn: "" },
      { id: "p2", name: "Kartoffeln", expiresOn: "" },
      { id: "p3", name: "Most", expiresOn: "" },
      { id: "p4", name: "Erdbeeren", expiresOn: "2026-07-31" },
    ],
    posts: [
      {
        id: "s1-post-1",
        kind: "availability",
        productId: "p1",
        productName: "Eier",
        text: "",
        price: "CHF 6.50 / 10 Stk",
        until: "",
        photos: [],
        createdAt: "2026-02-06T08:15:00.000Z",
      },
      {
        id: "s1-post-2",
        kind: "promo",
        productId: "",
        productName: "",
        text: "Heute Raclette-Verkauf ab 16:00 — solange Vorrat reicht.",
        price: "",
        until: "2026-02-06T16:00:00.000Z",
        photos: [],
        createdAt: "2026-02-06T06:40:00.000Z",
      },
    ],
  },

  {
    id: 2,
    account: {
      displayName: "Marco Steiner",
      shopName: "Chäslädeli Brugg",
    },
    profile: {
      name: "Chäslädeli Brugg",
      type: "Chäslädeli",
      phone: "+41 79 222 33 44",
      link: "https://example.com/chaeslaedeli-brugg",
      address: "Marktgasse 3, 5200 Brugg",
      lat: "47.480820",
      lon: "8.206110",
      hours: {
        mon: { closed: false, from: "10:00", to: "18:30" },
        tue: { closed: false, from: "10:00", to: "18:30" },
        wed: { closed: false, from: "10:00", to: "18:30" },
        thu: { closed: false, from: "10:00", to: "18:30" },
        fri: { closed: false, from: "10:00", to: "19:00" },
        sat: { closed: false, from: "09:00", to: "14:00" },
        sun: { closed: true, from: "", to: "" },
      },
      vacation: { enabled: false, from: "", to: "", note: "" },
    },
    sortiment: [
      { id: "p1", name: "Raclette", expiresOn: "" },
      { id: "p2", name: "Mutschli", expiresOn: "" },
      { id: "p3", name: "Bergkäse", expiresOn: "" },
    ],
    posts: [
      {
        id: "s2-post-1",
        kind: "new",
        productId: "p3",
        productName: "Bergkäse",
        text: "",
        price: "CHF 4.20 / 100g",
        until: "",
        photos: [],
        createdAt: "2026-02-05T14:10:00.000Z",
      },
      {
        id: "s2-post-2",
        kind: "promo",
        productId: "",
        productName: "",
        text: "Degustation: Mutschli & Bergkäse — heute bis 18:00.",
        price: "",
        until: "2026-02-06T18:00:00.000Z",
        photos: [],
        createdAt: "2026-02-06T09:05:00.000Z",
      },
    ],
  },

  {
    id: 3,
    account: {
      displayName: "Lea Baumann",
      shopName: "Bäckerei Dorf",
    },
    profile: {
      name: "Bäckerei Dorf",
      type: "Bäckerei",
      phone: "+41 79 333 44 55",
      link: "https://example.com/baeckerei-dorf",
      address: "Dorfplatz 1, 5200 Brugg",
      lat: "47.483260",
      lon: "8.213940",
      hours: {
        mon: { closed: false, from: "06:30", to: "17:00" },
        tue: { closed: false, from: "06:30", to: "17:00" },
        wed: { closed: false, from: "06:30", to: "17:00" },
        thu: { closed: false, from: "06:30", to: "17:00" },
        fri: { closed: false, from: "06:30", to: "17:30" },
        sat: { closed: false, from: "07:00", to: "13:00" },
        sun: { closed: true, from: "", to: "" },
      },
      vacation: { enabled: false, from: "", to: "", note: "" },
    },
    sortiment: [
      { id: "p1", name: "Zopf", expiresOn: "" },
      { id: "p2", name: "Sauerteigbrot", expiresOn: "" },
      { id: "p3", name: "Nussgipfel", expiresOn: "" },
    ],
    posts: [
      {
        id: "s3-post-1",
        kind: "availability",
        productId: "p1",
        productName: "Zopf",
        text: "",
        price: "CHF 5.20",
        until: "",
        photos: [],
        createdAt: "2026-02-06T05:30:00.000Z",
      },
      {
        id: "s3-post-2",
        kind: "promo",
        productId: "",
        productName: "",
        text: "Frisch aus dem Ofen: Sauerteigbrot & Nussgipfel — jetzt im Laden.",
        price: "",
        until: "",
        photos: [],
        createdAt: "2026-02-06T06:10:00.000Z",
      },
    ],
  },
];

// helper (optional) to find a shop quickly
export function getMockShopById(id) {
  return mockShops.find((s) => String(s.id) === String(id)) || mockShops[0];
}
