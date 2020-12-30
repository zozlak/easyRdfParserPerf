.libPaths()
install.packages(setdiff(c('dplyr', 'jsonlite', 'ggplot2', 'magrittr', 'svglite'), installed.packages()[, 'Package']))
library(magrittr)
data = jsonlite::read_json('output/data.json', simplifyVector = TRUE) %>%
  dplyr::as_tibble() %>%
  dplyr::mutate(
    class = sub('^.*[\\]', '', .data$class),
    file  = sub('[.][^.]+$', '', .data$dataFile),
    format = sub('^.*[.]', '', .data$dataFile),
    triplesPerSecond = .data$triplesCount / .data$time,
    test = paste(.data$class, .data$file, sep = '\n')
  )

plot = data %>% ggplot2::ggplot(ggplot2::aes(x = test, y = time, color = class)) +
  ggplot2::geom_violin() +
  ggplot2::ggtitle('Parsing time') +
  ggplot2::facet_wrap(dplyr::vars(format), ncol = 1) +
  ggplot2::theme_light()
ggplot2::ggsave('output/parsing_time.svg', plot, 'svg', width = 10, height = 10)
plot = data %>% ggplot2::ggplot(ggplot2::aes(x = test, y = triplesPerSecond, color = class)) +
  ggplot2::geom_violin() +
  ggplot2::ggtitle('Throughput') +
  ggplot2::facet_wrap(dplyr::vars(format), ncol = 1) +
  ggplot2::theme_light()
ggplot2::ggsave('output/troughput.svg', plot, 'svg', width = 10, height = 10)
plot = data %>% ggplot2::ggplot(ggplot2::aes(x = test, y = memoryMb, color = class)) +
  ggplot2::geom_violin() +
  ggplot2::ggtitle('Memory usage') +
  ggplot2::facet_wrap(dplyr::vars(format), ncol = 1) +
  ggplot2::theme_light()
ggplot2::ggsave('output/memory_usage.svg', plot, 'svg', width = 10, height = 10)
