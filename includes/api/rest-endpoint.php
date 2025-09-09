<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', function() {
    register_rest_route( 'sdg-chatbot/v1', '/query', array(
        'methods'  => 'POST',
        'callback' => 'sdg_chatbot_handle_query',
        'permission_callback' => '__return_true',
        'args' => array(
            'message' => array(
                'required' => true,
                'type'     => 'string',
            ),
        ),
    ) );
} );

/**
 * Helper function to check for multiple keywords in a string.
 *
 * @param string $string The string to search in.
 * @param array $keywords The keywords to search for.
 * @return bool True if any keyword is found, false otherwise.
 */
function sdg_chatbot_has_keyword( $string, $keywords ) {
    foreach ( $keywords as $keyword ) {
        if ( strpos( $string, $keyword ) !== false ) {
            return true;
        }
    }
    return false;
}

function sdg_chatbot_handle_query( WP_REST_Request $request ) {
    $message = sanitize_text_field( $request->get_param( 'message' ) );
    if ( $message === '' ) {
        return array( 'reply' => 'Please type a message.' );
    }

    $message_lower = strtolower( $message );
    $bot_reply = '';
    $response_buttons = []; // For interactive buttons

    // --- Conversational Knowledge Base ---

    // --- Intent: Greeting & Core Purpose ---
    if ( sdg_chatbot_has_keyword($message_lower, ['hello', 'hi', 'hei']) ) {
        $bot_reply = 'Hello! I can help you create your own SDG report. You can ask me how to get started, select goals, or understand the targets. What would you like to do?';
        $response_buttons = [
            ['label' => 'Start a new report', 'action' => 'how to start'],
            ['label' => 'What are the SDGs?', 'action' => 'what are sdgs'],
        ];
    }
    // --- Intent: Bot Personality & Social ---
    elseif ( sdg_chatbot_has_keyword($message_lower, ['who are you', 'are you a bot']) ) {
        $bot_reply = "I'm an automated assistant designed to help you build your own SDG compliance report using the tools on this website.";
    }
    elseif ( sdg_chatbot_has_keyword($message_lower, ['thanks', 'thank you', 'bye']) ) {
        $bot_reply = "You're welcome! Let me know if you need anything else for your report.";
    }
    elseif ( sdg_chatbot_has_keyword($message_lower, ["i'm good", "im good", 'fine', 'okay', 'alright', 'not bad']) ) {
        $bot_reply = "Great! Are you ready to start working on your SDG report?";
        $response_buttons = [
            ['label' => 'Yes, how do I start?', 'action' => 'how to start'],
            ['label' => 'What are the SDGs?', 'action' => 'what are sdgs'],
        ];
    }
    // --- Intent: How to Create a Report (NEW CORE FUNCTIONALITY) ---
    elseif ( sdg_chatbot_has_keyword($message_lower, ['how to start', 'create a report', 'get started']) ) {
        $bot_reply = "To start, you'll need to select the Sustainable Development Goals that are relevant to you. You can browse through the 17 goals and choose the ones you want to include in your report.";
    }
    elseif ( sdg_chatbot_has_keyword($message_lower, ['select goals', 'choose a goal']) ) {
        $bot_reply = "You can select goals from the main list. Once you choose a goal, you can then see the 169 specific targets associated with them to add to your report.";
    }
     elseif ( sdg_chatbot_has_keyword($message_lower, ['target', '169 targets']) ) {
        $bot_reply = "Yes, there are 169 targets across the 17 goals. These are specific, measurable objectives. When you select a goal for your report, you can then choose which of its targets you want to report on.";
    }
    elseif ( sdg_chatbot_has_keyword($message_lower, ['download', 'generate', 'finish report']) ) {
        $bot_reply = "Once you have selected all your desired goals and targets, you will be able to generate and download a complete report based on your selections.";
    }
    // --- Intent: Learn About SDGs in General ---
    elseif ( sdg_chatbot_has_keyword($message_lower, ['what are sdgs', 'what is an sdg', 'explain the goals']) ) {
        $bot_reply = 'The Sustainable Development Goals (SDGs) are 17 interlinked global goals from the UN. On this site, you can select which of these goals you want to report on. Which goal would you like to know more about?';
        $response_buttons = [
            ['label' => 'SDG 13: Climate Action', 'action' => 'sdg 13'],
            ['label' => 'SDG 5: Gender Equality', 'action' => 'sdg 5'],
        ];
    }

    // === NEW KNOWLEDGE FROM PDFS (kept as-is) ===

    // Benefits of SDG Reporting
    elseif ( sdg_chatbot_has_keyword($message_lower, ['benefits of sdg', 'why measure', 'advantages']) ) {
        $bot_reply = "Measuring your SDG impact helps identify risks and opportunities, strengthen your brand, drive innovation, and improve stakeholder trust.";
    }

    // Challenges in SDG Reporting
    elseif ( sdg_chatbot_has_keyword($message_lower, ['challenges', 'barriers', 'problems']) ) {
        $bot_reply = "Common challenges include complex frameworks, lack of knowledge, lack of guidance, and action barriers that prevent participation.";
    }

    // UN Goals Hub Features
    elseif ( sdg_chatbot_has_keyword($message_lower, ['un goals hub', 'platform features']) ) {
        $bot_reply = "The UN Goals Hub offers training resources, an SDG quiz, business assessment tools, and custom reporting features to make sustainability tracking easier.";
    }

    // SDG Methodology
    elseif ( sdg_chatbot_has_keyword($message_lower, ['sdg methodology', 'sdg cards', 'methodology']) ) {
        $bot_reply = "Our SDG Cards methodology uses interactive workshops to discuss and prioritise goals, map your impact, and set ambitions that integrate into your business strategy.";
    }

    // Common SDG Mistakes
    elseif ( sdg_chatbot_has_keyword($message_lower, ['mistakes', 'sdg washing', 'common errors']) ) {
        $bot_reply = "Avoid SDG washing, cherry-picking only easy goals, ignoring detailed targets, and working in isolation from SDG principles.";
    }

    // Project Timeline
    elseif ( sdg_chatbot_has_keyword($message_lower, ['project timeline', 'roadmap']) ) {
        $bot_reply = "2024: Build the foundation. 2025: Enhance features and expand reach. 2026: Scale, market, and plan for the future.";
    }

    // Success Stories
    elseif ( sdg_chatbot_has_keyword($message_lower, ['success story', 'case study']) ) {
        $bot_reply = "Examples include Sjøholmen Kulturhus improving sustainability reports and Aqua-360 achieving measurable sustainability results.";
    }

    // === END NEW KNOWLEDGE ===

    // --- Intent: Ask About a Specific SDG (Upgraded with bullets) ---

    // SDG 1: No Poverty
    elseif ( sdg_chatbot_has_keyword($message_lower, ['poverty', 'sdg 1']) ) {
        $bot_reply =
"SDG 1: No Poverty
- Goal: End poverty in all its forms everywhere.
- Why it matters: Poverty undermines access to food, education, health, and resilience.
- Example targets:
  - Eradicate extreme poverty for all people everywhere.
  - Reduce by at least half the proportion of people living in poverty.
  - Implement nationally appropriate social protection systems.
- In your report: Map current social impact, living-wage policies, supplier standards, and community programs.";
    }

    // SDG 2: Zero Hunger
    elseif ( sdg_chatbot_has_keyword($message_lower, ['hunger', 'sdg 2']) ) {
        $bot_reply =
"SDG 2: Zero Hunger
- Goal: End hunger, achieve food security and improved nutrition, and promote sustainable agriculture.
- Why it matters: Food security and sustainable agriculture are essential for health and stable societies.
- Example targets:
  - End hunger and ensure access to safe, nutritious, and sufficient food year-round.
  - End all forms of malnutrition, including for children and vulnerable groups.
  - Ensure sustainable, resilient food production systems.
- In your report: Consider supply-chain sourcing, food waste reduction, and support for local producers.";
    }

    // SDG 3: Good Health and Well-being
    elseif ( sdg_chatbot_has_keyword($message_lower, ['health', 'well-being', 'sdg 3']) ) {
        $bot_reply =
"SDG 3: Good Health and Well-being
- Goal: Ensure healthy lives and promote well-being for all at all ages.
- Why it matters: Health determines productivity, equality, and resilience.
- Example targets:
  - Reduce global maternal and child mortality.
  - Strengthen prevention and treatment of communicable and non-communicable diseases.
  - Promote mental health and well-being; reduce deaths and injuries from road traffic.
- In your report: Include workplace health/safety, mental health support, and community health initiatives.";
    }

    // SDG 4: Quality Education
    elseif ( sdg_chatbot_has_keyword($message_lower, ['education', 'school', 'sdg 4']) ) {
        $bot_reply =
"SDG 4: Quality Education
- Goal: Ensure inclusive and equitable quality education and promote lifelong learning opportunities for all.
- Why it matters: Education drives innovation, inclusion, and economic mobility.
- Example targets:
  - Ensure free, equitable primary and secondary education.
  - Increase youth and adult skills for employment and entrepreneurship.
  - Eliminate gender disparities and ensure equal access for vulnerable groups.
- In your report: Track training hours, upskilling programs, internships, and STEM/DEI education support.";
    }

    // SDG 5: Gender Equality
    elseif ( sdg_chatbot_has_keyword($message_lower, ['gender', 'equality', 'sdg 5']) ) {
        $bot_reply =
"SDG 5: Gender Equality
- Goal: Achieve gender equality and empower all women and girls.
- Why it matters: Equality boosts performance, innovation, and fairness.
- Example targets:
  - End all forms of discrimination and violence against women and girls.
  - Ensure women’s full and effective participation and equal leadership opportunities.
  - Ensure universal access to sexual and reproductive health and rights.
- In your report: Publish gender pay data, leadership ratios, anti-harassment policies, and inclusive hiring practices.";
    }

    // SDG 6: Clean Water and Sanitation
    elseif ( sdg_chatbot_has_keyword($message_lower, ['clean water', 'sanitation', 'sdg 6']) ) {
        $bot_reply =
"SDG 6: Clean Water and Sanitation
- Goal: Ensure availability and sustainable management of water and sanitation for all.
- Why it matters: Water security underpins health, industry, and ecosystems.
- Example targets:
  - Achieve universal and equitable access to safe and affordable drinking water.
  - Improve water quality and wastewater treatment; reduce pollution.
  - Increase water-use efficiency and ensure sustainable withdrawals.
- In your report: Measure water use, discharge quality, WASH access for staff, and watershed projects.";
    }

    // SDG 7: Affordable and Clean Energy
    elseif ( sdg_chatbot_has_keyword($message_lower, ['energy', 'sdg 7']) ) {
        $bot_reply =
"SDG 7: Affordable and Clean Energy
- Goal: Ensure access to affordable, reliable, sustainable, and modern energy for all.
- Why it matters: Clean energy cuts emissions and costs, powering inclusive growth.
- Example targets:
  - Increase the share of renewable energy in the global energy mix.
  - Double the global rate of improvement in energy efficiency.
  - Ensure universal access to affordable, reliable, modern energy services.
- In your report: Track Scope 2 reductions, renewable procurement (PPA/GOOs), and efficiency retrofits.";
    }

    // SDG 8: Decent Work and Economic Growth
    elseif ( sdg_chatbot_has_keyword($message_lower, ['work', 'economic growth', 'sdg 8']) ) {
        $bot_reply =
"SDG 8: Decent Work and Economic Growth
- Goal: Promote sustained, inclusive and sustainable economic growth, full and productive employment, and decent work for all.
- Why it matters: Quality jobs and inclusive growth build resilient economies.
- Example targets:
  - Improve resource efficiency in consumption and production; decouple growth from environmental degradation.
  - Protect labor rights and promote safe and secure working environments.
  - Reduce the proportion of youth not in employment, education, or training (NEET).
- In your report: Show job creation, safety KPIs, supplier labor standards, and productivity/efficiency gains.";
    }

    // SDG 9: Industry, Innovation and Infrastructure
    elseif ( sdg_chatbot_has_keyword($message_lower, ['industry', 'innovation', 'infrastructure', 'sdg 9']) ) {
        $bot_reply =
"SDG 9: Industry, Innovation and Infrastructure
- Goal: Build resilient infrastructure, promote inclusive and sustainable industrialization, and foster innovation.
- Why it matters: Infrastructure and R&D fuel competitiveness and climate solutions.
- Example targets:
  - Develop quality, reliable, sustainable, and resilient infrastructure.
  - Upgrade industries to adopt clean and environmentally sound technologies.
  - Enhance scientific research and innovation, including SME access to finance.
- In your report: Include R&D spend, low-carbon tech adoption, and infrastructure resilience metrics.";
    }

    // SDG 10: Reduced Inequalities
    elseif ( sdg_chatbot_has_keyword($message_lower, ['inequalit', 'sdg 10']) ) { // Catches 'inequality' and 'inequalities'
        $bot_reply =
"SDG 10: Reduced Inequalities
- Goal: Reduce inequality within and among countries.
- Why it matters: Inclusive growth improves stability and opportunity.
- Example targets:
  - Empower and promote the social, economic, and political inclusion of all.
  - Ensure equal opportunity and reduce inequalities of outcome.
  - Adopt policies for greater equality of outcomes, including fiscal and wage policies.
- In your report: Track pay equity, accessibility, inclusive hiring, and community access initiatives.";
    }

    // SDG 11: Sustainable Cities and Communities
    elseif ( sdg_chatbot_has_keyword($message_lower, ['cities', 'communities', 'sdg 11']) ) {
        $bot_reply =
"SDG 11: Sustainable Cities and Communities
- Goal: Make cities and human settlements inclusive, safe, resilient and sustainable.
- Why it matters: Urban areas drive emissions, innovation, and quality of life.
- Example targets:
  - Ensure access to adequate, safe, and affordable housing and basic services.
  - Provide access to safe, affordable, accessible, and sustainable transport systems.
  - Strengthen efforts to protect cultural and natural heritage; reduce disaster risk.
- In your report: Include mobility plans, building efficiency, resilience planning, and community engagement.";
    }

    // SDG 12: Responsible Consumption and Production
    elseif ( sdg_chatbot_has_keyword($message_lower, ['consumption', 'production', 'sdg 12']) ) {
        $bot_reply =
"SDG 12: Responsible Consumption and Production
- Goal: Ensure sustainable consumption and production patterns.
- Why it matters: Decoupling growth from resource use protects climate and nature.
- Example targets:
  - Implement sustainable consumption and production frameworks.
  - Substantially reduce waste generation through prevention, reduction, recycling, and reuse.
  - Encourage companies to adopt sustainable practices and sustainability reporting.
- In your report: Map material flows, circularity actions, supplier standards, and product lifecycle impacts.";
    }

    // SDG 13: Climate Action
    elseif ( sdg_chatbot_has_keyword($message_lower, ['climate', 'sdg 13']) ) {
        $bot_reply =
"SDG 13: Climate Action
- Goal: Take urgent action to combat climate change and its impacts.
- Why it matters: Climate risks affect operations, supply chains, and communities.
- Example targets:
  - Strengthen resilience and adaptive capacity to climate-related hazards and natural disasters.
  - Integrate climate measures into policies, strategies, and planning.
  - Improve education and awareness on climate mitigation, adaptation, and early warning.
- In your report: Include GHG inventory (Scopes 1–3), transition plan, adaptation measures, and climate governance.";
    }

    // SDG 14: Life Below Water
    elseif ( sdg_chatbot_has_keyword($message_lower, ['ocean', 'sea', 'life below water', 'sdg 14']) ) {
        $bot_reply =
"SDG 14: Life Below Water
- Goal: Conserve and sustainably use the oceans, seas, and marine resources.
- Why it matters: Oceans regulate climate, feed billions, and support livelihoods.
- Example targets:
  - Reduce marine pollution of all kinds, especially from land-based activities.
  - Sustainably manage and protect marine and coastal ecosystems.
  - Regulate harvesting and end overfishing, illegal, unreported, and unregulated fishing.
- In your report: Address plastic footprint, wastewater, sustainable seafood sourcing, and coastal stewardship.";
    }

    // SDG 15: Life on Land
    elseif ( sdg_chatbot_has_keyword($message_lower, ['life on land', 'forest', 'desert', 'sdg 15']) ) {
        $bot_reply =
"SDG 15: Life on Land
- Goal: Protect, restore and promote sustainable use of terrestrial ecosystems, sustainably manage forests, combat desertification, and halt biodiversity loss.
- Why it matters: Healthy ecosystems support resources, climate, and resilience.
- Example targets:
  - Ensure conservation, restoration, and sustainable use of terrestrial ecosystems.
  - Combat desertification and restore degraded land and soil.
  - Take urgent action to end poaching and trafficking of protected species.
- In your report: Include no-deforestation sourcing, habitat restoration, and nature-positive projects.";
    }

    // SDG 16: Peace, Justice and Strong Institutions
    elseif ( sdg_chatbot_has_keyword($message_lower, ['peace', 'justice', 'sdg 16']) ) {
        $bot_reply =
"SDG 16: Peace, Justice and Strong Institutions
- Goal: Promote peaceful and inclusive societies, provide access to justice for all, and build effective, accountable, and inclusive institutions.
- Why it matters: Trust, rule of law, and transparency enable sustainable development.
- Example targets:
  - Significantly reduce all forms of violence and related death rates.
  - Develop effective, accountable, and transparent institutions.
  - Ensure responsive, inclusive, participatory decision-making at all levels.
- In your report: Publish ethics policies, grievance channels, anti-corruption training, and transparency metrics.";
    }

    // SDG 17: Partnerships for the Goals
    elseif ( sdg_chatbot_has_keyword($message_lower, ['partnerships', 'sdg 17']) ) {
        $bot_reply =
"SDG 17: Partnerships for the Goals
- Goal: Strengthen the means of implementation and revitalize the global partnership for sustainable development.
- Why it matters: Collaboration accelerates resources, innovation, and scale.
- Example targets:
  - Enhance multi-stakeholder partnerships that mobilize and share knowledge, expertise, and technology.
  - Promote sustainable finance, capacity-building, and technology transfer.
  - Encourage data, monitoring, and accountability for the SDGs.
- In your report: Map partners, joint initiatives, shared KPIs, and data/reporting collaborations.";
    }

    // --- Intent: Ask for Human Help ---
    elseif ( sdg_chatbot_has_keyword($message_lower, ['human', 'person', 'agent', 'talk to someone']) ) {
        $contact_url = site_url('/contact'); // You can update this URL
        $bot_reply = "Of course. If you need help from a person, the best way to get in touch is through the contact page. Here is the link: <a href='{$contact_url}' target='_blank'>Contact Us</a>.";
    }
    elseif ( sdg_chatbot_has_keyword($message_lower, ['contact', 'help']) ) {
        $contact_url = site_url('/contact'); // You can update this URL
        $bot_reply = "If you need further assistance with your report, you can reach our team through the main contact form on our website: <a href='{$contact_url}' target='_blank'>Contact Us</a>.";
    }
    // --- Default Fallback Response ---
    else {
        $bot_reply = "I'm sorry, I'm not sure how to answer that. You can ask me how to start a report, select a goal, or what the targets are.";
        $response_buttons = [
            ['label' => 'How to start a report', 'action' => 'how to start'],
            ['label' => 'Contact Support', 'action' => 'contact'],
        ];
    }

    // Logging (if enabled)
    $o = get_option( 'sdg_chatbot_options', array() );
    $logging_enabled = ! empty( $o['chatbot_enable_logging'] );

    if ( $logging_enabled ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sdg_chatbot_logs';
        $wpdb->insert(
            $table,
            array(
                'user_message' => $message,
                'bot_reply'    => $bot_reply,
            ),
            array( '%s', '%s' )
        );
    }

    // Return both the response text and interactive buttons
    return array(
        'reply' => nl2br( esc_html( $bot_reply ) ),
        'buttons' => $response_buttons
    );
}