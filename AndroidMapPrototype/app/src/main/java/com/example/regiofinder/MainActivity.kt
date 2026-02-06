package com.example.regiofinder

import android.Manifest
import android.annotation.SuppressLint
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.os.Bundle
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.annotation.RequiresPermission
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import com.google.android.material.button.MaterialButton
import com.google.android.material.floatingactionbutton.FloatingActionButton
import org.maplibre.android.MapLibre
import org.maplibre.android.WellKnownTileServer
import org.maplibre.android.camera.CameraPosition
import org.maplibre.android.camera.CameraUpdateFactory
import org.maplibre.android.geometry.LatLng
import org.maplibre.android.location.LocationComponentActivationOptions
import org.maplibre.android.location.modes.CameraMode
import org.maplibre.android.location.modes.RenderMode
import org.maplibre.android.maps.MapView
import org.maplibre.android.maps.MapLibreMap
import org.maplibre.android.maps.Style
import org.maplibre.android.style.expressions.Expression
import org.maplibre.android.style.layers.PropertyFactory
import org.maplibre.android.style.layers.SymbolLayer
import org.maplibre.android.style.sources.GeoJsonSource
import org.maplibre.geojson.Feature
import org.maplibre.geojson.FeatureCollection
import org.maplibre.geojson.Point

class MainActivity : AppCompatActivity() {

    private lateinit var mapView: MapView
    private var map: MapLibreMap? = null
    private var style: Style? = null

    private val wmtsTiles =
        "https://wmts.geo.admin.ch/1.0.0/ch.swisstopo.pixelkarte-farbe/default/current/3857/{z}/{x}/{y}.jpeg"

    // Emoji cycle for now (later map to shop type)
    private val emojiCycle = listOf("🧀", "🥖", "🥩", "🥕", "🍯")
    private var emojiIndex = 0

    // Add-mode state
    private var isPickingLocation = false

    // Marker data (in-memory for now)
    private val markerFeatures = mutableListOf<Feature>()

    @SuppressLint("MissingPermission")
    private val requestLocationPermission =
        registerForActivityResult(ActivityResultContracts.RequestPermission()) { granted ->
            if (granted) {
                enableMyLocation()
            } else {
                Toast.makeText(this, "Location permission denied", Toast.LENGTH_SHORT).show()
            }
        }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        MapLibre.getInstance(
            applicationContext,
            getString(R.string.maplibre_key),
            WellKnownTileServer.MapLibre
        )

        setContentView(R.layout.activity_main)

        mapView = findViewById(R.id.mapView)
        mapView.onCreate(savedInstanceState)

        mapView.getMapAsync { m ->
            map = m

            val styleJson = """
            {
              "version": 8,
              "sources": {
                "geoadmin-wmts": {
                  "type": "raster",
                  "tiles": ["$wmtsTiles"],
                  "tileSize": 256
                }
              },
              "layers": [
                { "id": "geoadmin-wmts-layer", "type": "raster", "source": "geoadmin-wmts" }
              ]
            }
            """.trimIndent()

            m.setStyle(Style.Builder().fromJson(styleJson)) { s ->
                style = s

                // Start position (Aargau-ish)
                m.cameraPosition = CameraPosition.Builder()
                    .target(LatLng(47.39, 8.05))
                    .zoom(9.5)
                    .build()

                setupEmojiMarkerLayer(s)
                hookUi(m)
                hookMapClicks(m)
                maybeEnableLocation()
            }
        }
    }

    private fun hookUi(m: MapLibreMap) {
        findViewById<MaterialButton>(R.id.btnZoomIn).setOnClickListener {
            m.animateCamera(CameraUpdateFactory.zoomIn())
        }
        findViewById<MaterialButton>(R.id.btnZoomOut).setOnClickListener {
            m.animateCamera(CameraUpdateFactory.zoomOut())
        }
        findViewById<MaterialButton>(R.id.btnMyLocation).setOnClickListener {
            maybeEnableLocation(forceRequest = true)

            // If already enabled, recenter
            val loc = m.locationComponent.lastKnownLocation
            if (loc != null) {
                m.animateCamera(
                    CameraUpdateFactory.newLatLngZoom(
                        LatLng(loc.latitude, loc.longitude),
                        15.0
                    )
                )
            } else {
                Toast.makeText(this, "No location yet 📍", Toast.LENGTH_SHORT).show()
            }
        }

        findViewById<FloatingActionButton>(R.id.fabAdd).setOnClickListener {
            isPickingLocation = true
            Toast.makeText(this, "Tap map to add shop 📌", Toast.LENGTH_SHORT).show()
        }
    }

    private fun hookMapClicks(m: MapLibreMap) {
        // Single click handler:
        // 1) if user is picking a location -> add new marker
        // 2) else if user clicked an existing marker -> show toast
        m.addOnMapClickListener { latLng ->
            if (isPickingLocation) {
                val emoji = emojiCycle[emojiIndex % emojiCycle.size]
                emojiIndex++

                addEmojiMarker(latLng, emoji)
                isPickingLocation = false

                Toast.makeText(
                    this,
                    "Added $emoji at ${latLng.latitude.format(5)}, ${latLng.longitude.format(5)}",
                    Toast.LENGTH_SHORT
                ).show()
                return@addOnMapClickListener true
            }

            // Not picking: check if user clicked a marker
            val s = style ?: return@addOnMapClickListener false
            val screenPoint = m.projection.toScreenLocation(latLng)
            val hits = m.queryRenderedFeatures(screenPoint, "shops-layer")
            if (hits.isNotEmpty()) {
                val e = hits[0].getStringProperty("emoji")
                Toast.makeText(this, "Shop: $e", Toast.LENGTH_SHORT).show()
                return@addOnMapClickListener true
            }

            false
        }
    }

    private fun setupEmojiMarkerLayer(s: Style) {
        // 1) Source with empty collection
        val source = GeoJsonSource("shops-source", FeatureCollection.fromFeatures(arrayOf()))
        s.addSource(source)

        // 2) Pre-generate bitmap icons for each emoji and add as style images
        for (emoji in emojiCycle) {
            val id = "emoji-$emoji"
            if (s.getImage(id) == null) {
                s.addImage(id, emojiBitmap(emoji))
            }
        }

        // 3) Symbol layer: icon image is taken from feature property "iconId"
        val layer = SymbolLayer("shops-layer", "shops-source")
            .withProperties(
                PropertyFactory.iconImage(Expression.get("iconId")),
                PropertyFactory.iconAllowOverlap(true),
                PropertyFactory.iconIgnorePlacement(true)
            )

        s.addLayer(layer)
    }

    private fun addEmojiMarker(latLng: LatLng, emoji: String) {
        val point = Point.fromLngLat(latLng.longitude, latLng.latitude)
        val feature = Feature.fromGeometry(point).apply {
            addStringProperty("emoji", emoji)
            addStringProperty("iconId", "emoji-$emoji")
        }

        markerFeatures.add(feature)

        val s = style ?: return
        val source = s.getSourceAs<GeoJsonSource>("shops-source") ?: return
        source.setGeoJson(FeatureCollection.fromFeatures(markerFeatures))
    }

    private fun maybeEnableLocation(forceRequest: Boolean = false) {
        val granted = ContextCompat.checkSelfPermission(
            this,
            Manifest.permission.ACCESS_FINE_LOCATION
        ) == PackageManager.PERMISSION_GRANTED

        if (granted) {
            enableMyLocation()
        } else if (forceRequest) {
            requestLocationPermission.launch(Manifest.permission.ACCESS_FINE_LOCATION)
        }
    }

    @RequiresPermission(allOf = [Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION])
    private fun enableMyLocation() {
        val m = map ?: return
        val s = style ?: return

        val locationComponent = m.locationComponent
        if (!locationComponent.isLocationComponentActivated) {
            val options = LocationComponentActivationOptions.builder(this, s).build()
            locationComponent.activateLocationComponent(options)
        }

        locationComponent.isLocationComponentEnabled = true
        locationComponent.cameraMode = CameraMode.TRACKING
        locationComponent.renderMode = RenderMode.COMPASS
    }

    private fun emojiBitmap(emoji: String): Bitmap {
        // Simple emoji -> bitmap icon (no extra libs)
        val sizePx = 96
        val bmp = Bitmap.createBitmap(sizePx, sizePx, Bitmap.Config.ARGB_8888)
        val c = Canvas(bmp)

        // transparent bg
        c.drawColor(Color.TRANSPARENT)

        val paint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
            color = Color.BLACK
            textAlign = Paint.Align.CENTER
            textSize = 64f
        }

        // Draw emoji roughly centered
        val x = sizePx / 2f
        val y = sizePx / 2f - (paint.descent() + paint.ascent()) / 2f
        c.drawText(emoji, x, y, paint)
        return bmp
    }

    private fun Double.format(decimals: Int): String = "%.${decimals}f".format(this)

    // MapView lifecycle
    override fun onStart() { super.onStart(); mapView.onStart() }
    override fun onResume() { super.onResume(); mapView.onResume() }
    override fun onPause() { mapView.onPause(); super.onPause() }
    override fun onStop() { mapView.onStop(); super.onStop() }
    override fun onLowMemory() { super.onLowMemory(); mapView.onLowMemory() }
    override fun onDestroy() { mapView.onDestroy(); super.onDestroy() }
    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        mapView.onSaveInstanceState(outState)
    }
}
