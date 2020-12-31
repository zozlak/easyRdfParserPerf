localLib = path.expand('~/R')
dir.create(localLib, showWarnings = FALSE)
.libPaths(c(localLib, .libPaths()))
options(scipen = 100)
install.packages(setdiff(c('dplyr', 'jsonlite', 'ggplot2', 'magrittr', 'svglite'), installed.packages()[, 'Package']), localLib)
library(magrittr)
data = list()
for (f in list.files('results', 'data.*json', full.names = TRUE, recursive = TRUE)) {
  data[[length(data) + 1]] = jsonlite::read_json(f, simplifyVector = TRUE)
}
data = dplyr::bind_rows(data) %>%
  dplyr::as_tibble()
aggData = data %>%
  dplyr::filter(!is.na(time)) %>%
  dplyr::mutate(
    class = sub('^.*[\\]', '', .data$class),
    file  = sub('[.][^.]+$', '', .data$dataFile),
    format = sub('^.*[.]', '', .data$dataFile),
    triplesPerSecond = .data$triplesCount / .data$time,
    test = paste(.data$class, .data$file, sep = '\n'),
    oldEasyRdfVersion = as.character(grepl('EasyRdf-[0-9][.][0-9][.][0-9]', .data$class))
  ) %>% 
  dplyr::group_by(class, format, triplesCount) %>%
  dplyr::arrange(class, format, triplesCount, time) %>%
  dplyr::filter(dplyr::row_number() == ceiling(dplyr::n() / 2)) %>%
  dplyr::ungroup()

plot = aggData %>%
  ggplot2::ggplot(ggplot2::aes(x = triplesCount, y = time, color = class, linetype = oldEasyRdfVersion)) +
  ggplot2::geom_point() +
  ggplot2::geom_line() +
  ggplot2::ggtitle('Median parsing time (log2 scale) vs triples count') +
  ggplot2::facet_wrap(dplyr::vars(format), ncol = 1) +
  ggplot2::scale_y_continuous(trans = 'log2') +
  ggplot2::theme_light()
plot
ggplot2::ggsave('results/parsing_time.svg', plot, 'svg', width = 10, height = 10)
plot = aggData %>% ggplot2::ggplot(ggplot2::aes(x = triplesCount, y = triplesPerSecond, color = class, linetype = oldEasyRdfVersion)) +
  ggplot2::geom_point() +
  ggplot2::geom_line() +
  ggplot2::ggtitle('Triples parsed per second (log2 scale) vs triples count') +
  ggplot2::facet_wrap(dplyr::vars(format), ncol = 1) +
  ggplot2::scale_y_continuous(trans = 'log2') +
  ggplot2::theme_light()
plot
ggplot2::ggsave('results/troughput.svg', plot, 'svg', width = 10, height = 10)
plot = aggData %>% ggplot2::ggplot(ggplot2::aes(x = triplesCount, y = memoryMb, color = class, linetype = oldEasyRdfVersion)) +
  ggplot2::geom_point() +
  ggplot2::geom_line() +
  ggplot2::ggtitle('Memory usage') +
  ggplot2::facet_wrap(dplyr::vars(format), ncol = 1) +
  ggplot2::theme_light()
plot
ggplot2::ggsave('results/memory_usage.svg', plot, 'svg', width = 10, height = 10)
