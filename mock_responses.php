<?php
/**
 * Mock responses for when the Gemini API quota is exceeded
 * This provides a fallback to ensure the application remains functional
 */

// Array of pre-generated design suggestions for different room types
$MOCK_RESPONSES = [
    'living_room' => [
        "# Modern Living Room Design Concept

## Color Scheme
- **Main Colors**: Soft gray walls with white trim and accents of navy blue and mustard yellow
- **Secondary Colors**: Matte black metal accents and natural wood tones

## Furniture Layout
- **Seating**: Position a low-profile sectional sofa against the wall with the window, oriented to face the opposite wall
- **Coffee Table**: Round glass coffee table with black metal frame centered in the seating area
- **Media Center**: Floating media console in natural oak with black hardware mounted on the wall opposite the sofa
- **Accent Chairs**: Two mustard yellow accent chairs with slim metal legs placed perpendicular to the sofa

## Decorative Elements
- **Lighting**: Arched floor lamp with black metal base behind the sofa and minimalist pendant light overhead
- **Wall Decor**: Create a gallery wall with black and white photographs in thin black frames
- **Textiles**: Add texture with a geometric area rug in gray and white, plus navy blue throw pillows
- **Plants**: Include 2-3 large statement plants in matte black or concrete planters

## Additional Recommendations
- Install floating shelves in a staggered arrangement for displaying books and decorative objects
- Use smart lighting to create different moods throughout the day
- Consider a statement ceiling fixture like a modern sputnik chandelier for added visual interest"
    ],
    'bedroom' => [
        "# Modern Bedroom Design Concept

## Color Scheme
- **Main Colors**: Soft sage green walls with white trim and warm taupe accents
- **Secondary Colors**: Brushed brass hardware and natural linen textures

## Furniture Layout
- **Bed**: King-size platform bed with an upholstered headboard centered on the wall opposite the window
- **Nightstands**: Matching floating nightstands with minimalist design mounted on either side of the bed
- **Storage**: Low-profile dresser in light wood tone placed against the wall adjacent to the bed
- **Seating**: Small reading nook with a comfortable accent chair and side table in the corner by the window

## Decorative Elements
- **Lighting**: Pendant lights hanging at different heights on either side of the bed instead of traditional table lamps
- **Wall Decor**: Large abstract canvas in soothing colors above the bed
- **Textiles**: Layered bedding with crisp white sheets, a taupe duvet, and textured throw pillows
- **Window Treatments**: Floor-to-ceiling sheer curtains with blackout lining

## Additional Recommendations
- Add a large mirror with a slim brass frame to reflect light and make the space feel larger
- Install under-bed drawers for additional hidden storage
- Use a mix of textures (linen, wool, velvet) to add depth and interest to the space"
    ],
    'kitchen' => [
        "# Modern Kitchen Design Concept

## Color Scheme
- **Main Colors**: Matte white cabinetry with charcoal gray lower cabinets and white quartz countertops
- **Secondary Colors**: Brushed gold hardware and natural wood accents

## Layout
- **Cabinetry**: Floor-to-ceiling cabinets along the wall with the window to maximize storage
- **Island**: Large central island with waterfall countertop edge and seating for 4
- **Appliances**: Integrated appliances with panel-ready fronts to maintain clean lines
- **Sink**: Undermount single-bowl sink in white with a matte black faucet

## Decorative Elements
- **Lighting**: Cluster of three pendant lights above the island in mixed metals (gold and black)
- **Backsplash**: Geometric tile backsplash in white with light gray grout
- **Hardware**: Minimalist drawer pulls in brushed gold
- **Open Shelving**: Replace upper cabinets on one wall with floating wood shelves

## Additional Recommendations
- Install under-cabinet lighting for both functionality and ambiance
- Add a small herb garden near the window for fresh cooking ingredients
- Use clear glass canisters to store pantry staples for a clean, organized look
- Incorporate a built-in wine rack or beverage center if space allows"
    ],
    'office' => [
        "# Modern Home Office Design Concept

## Color Scheme
- **Main Colors**: Deep navy blue accent wall with remaining walls in soft white
- **Secondary Colors**: Walnut wood tones and brushed brass accents

## Furniture Layout
- **Desk**: Position a floating desk along the wall opposite the window to minimize glare on computer screens
- **Chair**: Ergonomic office chair in light gray upholstery with walnut and brass accents
- **Storage**: Wall-mounted modular shelving system combining closed cabinets and open display areas
- **Secondary Seating**: Small loveseat or daybed for reading or taking breaks

## Decorative Elements
- **Lighting**: Adjustable desk lamp with brass finish and a minimalist floor lamp near the secondary seating
- **Wall Decor**: Inspirational typography prints in thin brass frames arranged in a grid
- **Organization**: Desktop organizers in walnut wood and leather accents
- **Plants**: Low-maintenance plants like snake plants or ZZ plants in geometric planters

## Additional Recommendations
- Install a pegboard or wall grid system for flexible organization of supplies and inspiration
- Add a small area rug under the desk area to define the space and add warmth
- Use cable management solutions to keep technology cords neat and organized
- Consider a standing desk converter for ergonomic flexibility throughout the workday"
    ],
    'default' => [
        "# Modern Room Design Concept

## Color Scheme
- **Main Colors**: Neutral base with soft gray walls and white trim
- **Accent Colors**: Teal and mustard yellow with black metal accents

## Furniture Layout
- **Main Pieces**: Keep furniture low-profile and arranged to maximize flow through the space
- **Statement Piece**: Consider one bold furniture item like an accent chair or unique coffee table
- **Functionality**: Choose multi-functional pieces with clean lines and hidden storage

## Decorative Elements
- **Lighting**: Layer lighting with overhead fixtures, task lighting, and ambient options
- **Wall Decor**: Create a gallery wall with simple black frames or add one large statement piece
- **Textiles**: Incorporate different textures through pillows, throws, and window treatments
- **Plants**: Add life with 2-3 statement plants in modern planters

## Additional Recommendations
- Keep accessories minimal and purposeful to maintain a clean aesthetic
- Use mirrors strategically to enhance natural light and create the illusion of more space
- Consider smart home technology that blends seamlessly with the design
- Focus on quality materials that will stand the test of time"
    ]
];

/**
 * Get a mock response based on the room type and design style
 * 
 * @param string $roomType The type of room (living_room, bedroom, kitchen, etc.)
 * @return string The mock design suggestion
 */
function getMockResponse($roomType = 'default') {
    global $MOCK_RESPONSES;
    
    // Convert to lowercase and remove spaces for matching
    $roomType = strtolower(str_replace(' ', '_', $roomType));
    
    // Return the appropriate mock response or default if not found
    return $MOCK_RESPONSES[$roomType] ?? $MOCK_RESPONSES['default'];
}
