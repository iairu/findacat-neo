import { defineCollection, z } from 'astro:content';
import { glob } from 'astro/loaders';

// Docs live in src/content/docs/<locale>/<section>/<page>.mdx.
const docs = defineCollection({
  loader: glob({ pattern: '**/*.{md,mdx}', base: './src/content/docs' }),
  schema: z.object({
    title: z.string(),
    description: z.string(),
    // Sidebar group and position within it.
    section: z.enum(['start', 'guide', 'genetics', 'admin', 'api']),
    order: z.number().default(0),
    // Pages that only outline planned content.
    draft: z.boolean().default(false),
  }),
});

export const collections = { docs };
