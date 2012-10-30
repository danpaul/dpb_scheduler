###About

Dpb_scheduler is designed to be a light and flexible scheduling plugin. Events are entered as custom posts of type "dpb_scheduler" and displayed via calls made using shortcodes. There are two custom taxonomies for the dpb_scheduler events. These roughly correspond to the general Wordpress taxonomies "categories" and "tags". Although the regular taxonomies could have been used, the custom taxonomies word chosen to try to keep the plugin more modular and prevent tags and categories from getting mixed together.

When creating classification for your events, it is important to keep in mind a differenece in how the "event categories" and "event tags" are handled when called by the shortcodes. Simply, tags use the "or" logical operator while categories use the "and".

For example:

    [dpb_scheduler tags="turtle power, rad"]

will return events tagged "turtle power" _or_ "rad" (or both). While:

    [dpb_scheduler categories="tubular, Donatello"]
    
will only return events _both_ the "tubular" _and_ Donatello categories. The following:

    [dpb_scheduler categoires = "master splinter, Yoshi" tags="radiation effects, rat man"]
    
will return events that are in _both_ "master splinter" _and_ "Yoshi" categories _and_ have _one or both_ tags "radiation effects" _or_ "rat man".

###Entering events

To enter new events, simply go to the dpb_scheduler option and select "Create New" as if you were creating any other new post. Create a title, description, add event categories and tags. In the "Event information and options" box are a some important and largely self-explanatory options. Events are displayed chronologically. Once the current date has past the end date of the event, the "expired" category is automatically added to the event and it will no longer appear in the short code results.

###Admin panel

The admin panel has been customized so you can sort the events by start or end date. You can also filter the results to display only events in a particular category or with a particular tag.